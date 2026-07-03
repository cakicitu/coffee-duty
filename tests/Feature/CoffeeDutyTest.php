<?php

namespace Tests\Feature;

use App\Models\Bean;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoffeeDutyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_token' => 'test-token']);
    }

    private function apiHeaders(): array
    {
        return ['X-Api-Token' => 'test-token'];
    }

    // --- API auth ---

    public function test_api_rejects_requests_without_token()
    {
        $this->getJson('/api/user/all')->assertStatus(401);
    }

    public function test_api_rejects_requests_with_wrong_token()
    {
        $this->getJson('/api/user/all', ['X-Api-Token' => 'wrong'])->assertStatus(401);
    }

    public function test_api_accepts_requests_with_valid_token()
    {
        User::factory()->create(['drank' => 0, 'total' => 0]);
        $this->getJson('/api/user/all', $this->apiHeaders())->assertStatus(200);
    }

    public function test_api_allows_authenticated_web_users_without_token()
    {
        $user = User::factory()->create(['drank' => 0, 'total' => 0]);
        $this->actingAs($user)->getJson('/api/user/all')->assertStatus(200);
    }

    // --- Rotation logic ---

    public function test_select_job_picks_the_user_with_the_highest_drank_count()
    {
        $low = User::factory()->create(['drank' => 1, 'total' => 0, 'selected' => false]);
        $high = User::factory()->create(['drank' => 5, 'total' => 0, 'selected' => false]);

        $response = $this->postJson('/api/user/job/select', [], $this->apiHeaders());

        $response->assertStatus(200)->assertJsonPath('selected_user.id', $high->id);
        $this->assertTrue((bool) $high->fresh()->selected);
        $this->assertSame(0, (int) $high->fresh()->drank);
        $this->assertSame(5, (int) $high->fresh()->total);
        $this->assertFalse((bool) $low->fresh()->selected);
    }

    public function test_select_job_works_for_users_with_any_id_regression_missing_dollar_sign()
    {
        User::factory()->count(3)->create(['drank' => 0, 'total' => 0, 'selected' => false]);
        $target = User::factory()->create(['drank' => 9, 'total' => 0, 'selected' => false]);

        $this->postJson('/api/user/job/select', [], $this->apiHeaders())
            ->assertStatus(200)
            ->assertJsonPath('selected_user.id', $target->id);
    }

    public function test_toggle_finished_persists_the_incremented_count_regression_missing_save()
    {
        $user = User::factory()->create(['count' => 2, 'drank' => 0, 'total' => 0]);

        $this->postJson("/api/user/{$user->id}/toggle/finished", [], $this->apiHeaders())
            ->assertStatus(200);

        $this->assertSame(3, (int) $user->fresh()->count);
    }

    public function test_add_drank_increments_user_drank_and_bean_count()
    {
        $user = User::factory()->create(['drank' => 0, 'total' => 0]);
        $bean = Bean::create();

        $this->postJson("/api/user/{$user->id}/add/drank", [], $this->apiHeaders())
            ->assertStatus(200);

        $this->assertSame(1, (int) $user->fresh()->drank);
        $this->assertSame(1, (int) $bean->fresh()->count);
    }

    // --- Bean lifecycle ---

    public function test_storing_a_new_bean_finishes_the_previous_one()
    {
        $old = Bean::create();

        $this->postJson('/api/bean', [], $this->apiHeaders())->assertStatus(200);

        $this->assertTrue((bool) $old->fresh()->finished);
        $this->assertNotNull($old->fresh()->finished_at);
        $this->assertSame(1, Bean::where('finished', false)->count());
    }

    public function test_bean_index_returns_beans_and_current_bean()
    {
        Bean::create();

        $this->getJson('/api/bean', $this->apiHeaders())
            ->assertStatus(200)
            ->assertJsonStructure(['currentBeans', 'beans']);
    }

    public function test_beans_page_renders_without_an_active_bean_regression_500_on_empty()
    {
        $user = User::factory()->create(['drank' => 0, 'total' => 0]);

        $this->actingAs($user)->get('/beans')->assertStatus(200);
    }
}
