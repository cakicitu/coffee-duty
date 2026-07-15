<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\Bean;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Select a new user for coffee duty
     *
     * @return JsonResponse
     */
    public function selectJob()
    {
        // Unselect the current duty user and credit them for the beans they brought
        User::where('selected', true)->increment('count', 1, [
            'selected' => false,
        ]);

        // Users marked as finished sit out the rotation; fall back to everyone if all sit out
        $availableUsers = User::where('finished', false)->get();
        if ($availableUsers->isEmpty()) {
            $availableUsers = User::all();
        }

        if ($availableUsers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No users available for coffee duty',
            ], 422);
        }

        // Pick the highest drank; ties go to whoever has brought beans the fewest times
        $selectedUser = $availableUsers->sortBy('count')->sortByDesc('drank')->first();
        $total = $selectedUser->total + $selectedUser->drank;
        $selectedUser->update(['selected' => true, 'total' => $total, 'drank' => 0]);

        // Send push notification if the user has an IO config id assigned
        if ($selectedUser->io_id) {
            $this->sendPush($selectedUser->io_id);
        }

        // Return all users
        $allUsers = User::all();

        return response()->json([
            'success' => true,
            'users' => $allUsers,
            'selected_user' => $selectedUser,
        ]);
    }

    public function sendPush($id)
    {
        $apiKey = config('services.siedle.api_key');
        $baseUrl = config('services.siedle.base_url');

        // Push notifications are optional — skip if not configured
        if (! $apiKey || ! $baseUrl) {
            return null;
        }

        $response = Http::withHeaders([
            'accept' => 'application/json',
            'X-API-Key' => $apiKey,
        ])
            ->withoutVerifying()
            ->get(rtrim($baseUrl, '/').'/api-admin/api/v1/resources/1/io-configs/'.$id.'/set/true');

        return $response;
    }

    /**
     * Return all users; auto-selects a duty user if none is selected
     * so clients always see a coffee-getter.
     */
    public function getAllUsers()
    {
        $selectedUser = User::where('selected', true)->first();

        $availableUsers = User::where('finished', false)->get();
        if ($availableUsers->isEmpty()) {
            $availableUsers = User::all();
        }

        if (! $selectedUser && $availableUsers->isNotEmpty()) {
            $selectedUser = $availableUsers->sortBy('count')->sortByDesc('drank')->first();
            $total = $selectedUser->total + $selectedUser->drank;
            $selectedUser->update(['selected' => true, 'total' => $total, 'drank' => 0]);
        }

        // Return all users
        $allUsers = User::all();

        return response()->json([
            'success' => true,
            'users' => $allUsers,
            'selected_user' => $selectedUser,
        ]);
    }

    /**
     * Toggle the coffee-duty selection for a user (admin action).
     * Only one user can be selected at a time.
     */
    public function toggleSelected($id)
    {
        $user = User::findOrFail($id);
        $shouldSelect = ! $user->selected;

        if ($shouldSelect) {
            User::where('selected', true)->update(['selected' => false]);
        }

        $user->selected = $shouldSelect;
        $user->save();

        // Return all users
        $allUsers = User::all();

        return response()->json([
            'success' => true,
            'users' => $allUsers,
            'selected_user' => $user,
        ]);
    }

    /**
     * Toggle whether a user sits out the coffee rotation
     * (e.g. vacation or left the office).
     */
    public function toggleFinished($id)
    {
        $user = User::findOrFail($id);
        $user->finished = ! $user->finished;
        $user->save();

        // Return all users
        $allUsers = User::all();

        return response()->json([
            'success' => true,
            'users' => $allUsers,
        ]);
    }

    /**
     * Register one drunk cup for a user and count it against the current beans
     */
    public function addDrank($id)
    {
        $user = User::findOrFail($id);
        $user->increment('drank');

        // The cup still counts for the user even when no beans are tracked yet
        $currentBeans = Bean::where('finished', false)->first();
        if ($currentBeans) {
            $currentBeans->increment('count');
        }

        $allUsers = User::all();

        return response()->json([
            'success' => true,
            'users' => $allUsers,
        ]);
    }

    /**
     * Handle an API login request.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = $request->validateCredentials();

            // Return the authenticated user
            return response()->json([
                'success' => true,
                'user' => $user,
                'message' => 'Login successful',
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login',
            ], 500);
        }
    }
}
