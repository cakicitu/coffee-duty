<?php

namespace App\Http\Controllers;

use App\Models\CleaningDuty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CleaningController extends Controller
{
    /**
     * Render the cleaning page with the current duty and per-user stats
     */
    public function page()
    {
        $currentDuty = $this->ensureCurrentDuty();

        return Inertia::render('Cleaning', [
            'currentDuty' => $currentDuty,
            'stats' => $this->buildStats(),
        ]);
    }

    /**
     * Return the current week's cleaning duty (API endpoint for external clients)
     */
    public function current()
    {
        $currentDuty = $this->ensureCurrentDuty();

        return response()->json([
            'success' => (bool) $currentDuty,
            'current_duty' => $currentDuty,
            'stats' => $this->buildStats(),
        ], $currentDuty ? 200 : 422);
    }

    /**
     * Mark the current week's cleaning as done.
     * Session users may only confirm their own duty; token clients are trusted.
     */
    public function markDone(Request $request)
    {
        $currentDuty = $this->ensureCurrentDuty();

        if (! $currentDuty) {
            return response()->json([
                'success' => false,
                'message' => 'No users available for cleaning duty',
            ], 422);
        }

        $sessionUser = $request->user();
        if ($sessionUser && $sessionUser->id !== $currentDuty->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Only the assigned user can confirm the cleaning',
            ], 403);
        }

        if (! $currentDuty->done) {
            $currentDuty->done = true;
            $currentDuty->done_at = now();
            $currentDuty->save();
        }

        return response()->json([
            'success' => true,
            'current_duty' => $currentDuty->load('user'),
            'stats' => $this->buildStats(),
        ]);
    }

    // Returns the Sunday that starts the current cleaning week
    private function currentWeekStart(): Carbon
    {
        return now()->startOfWeek(Carbon::SUNDAY);
    }

    /**
     * Ensure a cleaning duty exists for the current week and return it.
     * The queue is ordered by user id and wraps around after the last assignee.
     */
    private function ensureCurrentDuty(): ?CleaningDuty
    {
        $weekStart = $this->currentWeekStart()->toDateString();

        $currentDuty = CleaningDuty::with('user')->where('week_start', $weekStart)->first();
        if ($currentDuty) {
            return $currentDuty;
        }

        // Users marked finished sit out; fall back to everyone if all sit out
        $eligibleUsers = User::where('finished', false)->orderBy('id')->get();
        if ($eligibleUsers->isEmpty()) {
            $eligibleUsers = User::orderBy('id')->get();
        }
        if ($eligibleUsers->isEmpty()) {
            return null;
        }

        $lastDuty = CleaningDuty::orderByDesc('week_start')->first();

        $nextUser = null;
        if ($lastDuty) {
            $nextUser = $eligibleUsers->first(fn (User $candidate) => $candidate->id > $lastDuty->user_id);
        }
        $nextUser = $nextUser ?? $eligibleUsers->first();

        return CleaningDuty::firstOrCreate(
            ['week_start' => $weekStart],
            ['user_id' => $nextUser->id],
        )->load('user');
    }

    /**
     * Per-user totals: how often selected, how often done, how often missed.
     * The current pending week counts as neither done nor missed yet.
     */
    private function buildStats()
    {
        $currentWeekStart = $this->currentWeekStart();

        return User::with('cleaningDuties')->orderBy('id')->get()->map(function (User $user) use ($currentWeekStart) {
            $userDuties = $user->cleaningDuties;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'selected' => $userDuties->count(),
                'done' => $userDuties->where('done', true)->count(),
                'missed' => $userDuties
                    ->filter(fn (CleaningDuty $duty) => ! $duty->done && $duty->week_start->lt($currentWeekStart))
                    ->count(),
            ];
        })->values();
    }
}
