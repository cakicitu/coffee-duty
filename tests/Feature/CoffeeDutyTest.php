<?php

namespace Tests\Feature;

use App\Models\Bean;
use App\Models\Dislike;
use App\Models\Like;
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

    public function test_toggle_finished_toggles_rotation_participation()
    {
        $user = User::factory()->create(['finished' => false, 'drank' => 0, 'total' => 0]);

        $this->postJson("/api/user/{$user->id}/toggle/finished", [], $this->apiHeaders())
            ->assertStatus(200);
        $this->assertTrue((bool) $user->fresh()->finished);

        $this->postJson("/api/user/{$user->id}/toggle/finished", [], $this->apiHeaders())
            ->assertStatus(200);
        $this->assertFalse((bool) $user->fresh()->finished);
    }

    public function test_select_job_skips_users_marked_finished()
    {
        $vacationUser = User::factory()->create(['drank' => 9, 'finished' => true, 'total' => 0]);
        $activeUser = User::factory()->create(['drank' => 1, 'finished' => false, 'total' => 0]);

        $this->postJson('/api/user/job/select', [], $this->apiHeaders())
            ->assertStatus(200)
            ->assertJsonPath('selected_user.id', $activeUser->id);
    }

    public function test_select_job_breaks_drank_ties_by_fewest_beans_brought()
    {
        $frequentBringer = User::factory()->create(['drank' => 2, 'count' => 5, 'total' => 0]);
        $rareBringer = User::factory()->create(['drank' => 2, 'count' => 1, 'total' => 0]);

        $this->postJson('/api/user/job/select', [], $this->apiHeaders())
            ->assertStatus(200)
            ->assertJsonPath('selected_user.id', $rareBringer->id);
    }

    public function test_select_job_returns_an_error_when_no_users_exist()
    {
        $this->postJson('/api/user/job/select', [], $this->apiHeaders())->assertStatus(422);
    }

    public function test_toggle_selected_keeps_only_one_user_selected()
    {
        $previouslySelected = User::factory()->create(['selected' => true, 'drank' => 0, 'total' => 0]);
        $newlySelected = User::factory()->create(['selected' => false, 'drank' => 0, 'total' => 0]);

        $this->postJson("/api/user/{$newlySelected->id}/toggle/selected", [], $this->apiHeaders())
            ->assertStatus(200);

        $this->assertFalse((bool) $previouslySelected->fresh()->selected);
        $this->assertTrue((bool) $newlySelected->fresh()->selected);
    }

    public function test_add_drank_works_without_an_active_bean_regression_null_crash()
    {
        $user = User::factory()->create(['drank' => 0, 'total' => 0]);

        $this->postJson("/api/user/{$user->id}/add/drank", [], $this->apiHeaders())
            ->assertStatus(200);

        $this->assertSame(1, (int) $user->fresh()->drank);
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

    public function test_new_beans_from_the_selected_user_advance_the_rotation()
    {
        $selectedUser = User::factory()->create(['selected' => true, 'drank' => 0, 'count' => 0, 'total' => 0]);
        $nextUser = User::factory()->create(['selected' => false, 'drank' => 4, 'total' => 0]);
        Bean::create();

        $this->postJson('/api/bean', ['user' => $selectedUser->id, 'name' => 'Fresh Pack'], $this->apiHeaders())
            ->assertStatus(200);

        $this->assertFalse((bool) $selectedUser->fresh()->selected);
        $this->assertSame(1, (int) $selectedUser->fresh()->count);
        $this->assertTrue((bool) $nextUser->fresh()->selected);
    }

    public function test_new_beans_from_another_user_credit_them_without_advancing_the_rotation()
    {
        $selectedUser = User::factory()->create(['selected' => true, 'drank' => 0, 'count' => 0, 'total' => 0]);
        $volunteer = User::factory()->create(['selected' => false, 'drank' => 4, 'count' => 0, 'total' => 1]);
        Bean::create();

        $this->postJson('/api/bean', ['user' => $volunteer->id], $this->apiHeaders())
            ->assertStatus(200);

        $this->assertTrue((bool) $selectedUser->fresh()->selected);
        $this->assertSame(1, (int) $volunteer->fresh()->count);
        $this->assertSame(5, (int) $volunteer->fresh()->total);
        $this->assertSame(0, (int) $volunteer->fresh()->drank);
    }

    // --- Bean evaluations ---

    public function test_a_user_cannot_like_the_same_bean_twice()
    {
        $user = User::factory()->create(['drank' => 0, 'total' => 0]);
        $bean = Bean::create();

        $this->actingAs($user)->postJson('/api/like', ['beanId' => $bean->id])->assertStatus(200);
        $this->actingAs($user)->postJson('/api/like', ['beanId' => $bean->id])->assertStatus(200);

        $this->assertSame(1, Like::where('user_id', $user->id)->where('bean_id', $bean->id)->count());
    }

    public function test_liking_a_bean_replaces_the_users_dislike()
    {
        $user = User::factory()->create(['drank' => 0, 'total' => 0]);
        $bean = Bean::create();

        $this->actingAs($user)->postJson('/api/dislike', ['beanId' => $bean->id])->assertStatus(200);
        $this->actingAs($user)->postJson('/api/like', ['beanId' => $bean->id])->assertStatus(200);

        $this->assertSame(0, Dislike::where('user_id', $user->id)->where('bean_id', $bean->id)->count());
        $this->assertSame(1, Like::where('user_id', $user->id)->where('bean_id', $bean->id)->count());
    }

    public function test_liking_a_nonexistent_bean_is_rejected()
    {
        $user = User::factory()->create(['drank' => 0, 'total' => 0]);

        $this->actingAs($user)->postJson('/api/like', ['beanId' => 999])->assertStatus(422);
    }
}
