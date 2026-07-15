<?php

namespace Tests\Feature;

use App\Models\CleaningDuty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CleaningDutyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_token' => 'test-token']);
        // A fixed Wednesday, so the current cleaning week starts Sunday 2026-07-12
        Carbon::setTestNow('2026-07-15 12:00:00');
    }

    private function apiHeaders(): array
    {
        return ['X-Api-Token' => 'test-token'];
    }

    public function test_first_duty_goes_to_the_user_with_the_lowest_id()
    {
        $firstUser = User::factory()->create(['drank' => 0, 'total' => 0]);
        User::factory()->create(['drank' => 0, 'total' => 0]);

        $this->getJson('/api/cleaning', $this->apiHeaders())
            ->assertStatus(200)
            ->assertJsonPath('current_duty.user_id', $firstUser->id)
            ->assertJsonPath('current_duty.week_start', '2026-07-12');
    }

    public function test_rotation_advances_to_the_next_user_id_after_a_week()
    {
        $firstUser = User::factory()->create(['drank' => 0, 'total' => 0]);
        $secondUser = User::factory()->create(['drank' => 0, 'total' => 0]);

        CleaningDuty::create(['user_id' => $firstUser->id, 'week_start' => '2026-07-05', 'done' => true]);

        $this->getJson('/api/cleaning', $this->apiHeaders())
            ->assertStatus(200)
            ->assertJsonPath('current_duty.user_id', $secondUser->id);
    }

    public function test_rotation_wraps_around_to_the_lowest_id()
    {
        $firstUser = User::factory()->create(['drank' => 0, 'total' => 0]);
        $lastUser = User::factory()->create(['drank' => 0, 'total' => 0]);

        CleaningDuty::create(['user_id' => $lastUser->id, 'week_start' => '2026-07-05', 'done' => true]);

        $this->getJson('/api/cleaning', $this->apiHeaders())
            ->assertStatus(200)
            ->assertJsonPath('current_duty.user_id', $firstUser->id);
    }

    public function test_rotation_skips_users_marked_finished()
    {
        $firstUser = User::factory()->create(['drank' => 0, 'total' => 0]);
        $vacationUser = User::factory()->create(['finished' => true, 'drank' => 0, 'total' => 0]);
        $thirdUser = User::factory()->create(['drank' => 0, 'total' => 0]);

        CleaningDuty::create(['user_id' => $firstUser->id, 'week_start' => '2026-07-05', 'done' => true]);

        $this->getJson('/api/cleaning', $this->apiHeaders())
            ->assertStatus(200)
            ->assertJsonPath('current_duty.user_id', $thirdUser->id);
    }

    public function test_the_duty_stays_with_the_same_user_within_one_week()
    {
        $user = User::factory()->create(['drank' => 0, 'total' => 0]);

        $this->getJson('/api/cleaning', $this->apiHeaders())->assertStatus(200);

        // Saturday of the same cleaning week must not advance the rotation
        Carbon::setTestNow('2026-07-18 12:00:00');
        $this->getJson('/api/cleaning', $this->apiHeaders())
            ->assertJsonPath('current_duty.user_id', $user->id);

        $this->assertSame(1, CleaningDuty::count());
    }

    public function test_the_assigned_user_can_confirm_the_cleaning()
    {
        $cleaner = User::factory()->create(['drank' => 0, 'total' => 0]);

        $this->actingAs($cleaner)->postJson('/api/cleaning/done')
            ->assertStatus(200)
            ->assertJsonPath('current_duty.done', true);

        $this->assertNotNull(CleaningDuty::first()->done_at);
    }

    public function test_other_users_cannot_confirm_the_cleaning()
    {
        $cleaner = User::factory()->create(['drank' => 0, 'total' => 0]);
        $otherUser = User::factory()->create(['drank' => 0, 'total' => 0]);

        $this->actingAs($otherUser)->postJson('/api/cleaning/done')->assertStatus(403);

        $this->assertFalse((bool) CleaningDuty::first()->done);
    }

    public function test_token_clients_can_confirm_the_cleaning()
    {
        User::factory()->create(['drank' => 0, 'total' => 0]);

        $this->postJson('/api/cleaning/done', [], $this->apiHeaders())
            ->assertStatus(200)
            ->assertJsonPath('current_duty.done', true);
    }

    public function test_stats_count_selected_done_and_missed_weeks()
    {
        $user = User::factory()->create(['drank' => 0, 'total' => 0]);

        // One done week, one missed week, and the current pending week
        CleaningDuty::create(['user_id' => $user->id, 'week_start' => '2026-06-28', 'done' => true]);
        CleaningDuty::create(['user_id' => $user->id, 'week_start' => '2026-07-05', 'done' => false]);

        $response = $this->getJson('/api/cleaning', $this->apiHeaders())->assertStatus(200);

        $userStats = collect($response->json('stats'))->firstWhere('id', $user->id);
        $this->assertSame(3, $userStats['selected']);
        $this->assertSame(1, $userStats['done']);
        $this->assertSame(1, $userStats['missed']);
    }

    public function test_cleaning_api_returns_an_error_when_no_users_exist()
    {
        $this->getJson('/api/cleaning', $this->apiHeaders())->assertStatus(422);
    }

    public function test_cleaning_page_renders()
    {
        $user = User::factory()->create(['drank' => 0, 'total' => 0]);

        $this->actingAs($user)->get('/cleaning')->assertStatus(200);
    }
}
