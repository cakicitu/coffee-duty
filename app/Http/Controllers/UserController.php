<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Select a new user for coffee duty
     *
     * @return JsonResponse
     */
    public function selectJob()
    {
        // Set the currently selected user to finished and not selected
        User::where('selected', true)->increment('count', 1, [
            'selected' => false,
            'finished' => true
        ]);

        // Get all users where finished is false
        $availableUsers = User::where('finished', false)->get();

        if ($availableUsers->isEmpty()) {
            // If no users are available (all finished), reset all users and start over
            User::query()->update([
                'finished' => false,
                'selected' => false
            ]);
            
            // Get all users again
            $availableUsers = User::all();
        }

        // Randomly select one user from available users
        $selectedUser = $availableUsers->first();
        
        // Set the selected user as selected
        $selectedUser->update(['selected' => true]);

        // Return all users
        $allUsers = User::all();

        return response()->json([
            'success' => true,
            'users' => $allUsers,
            'selected_user' => $selectedUser
        ]);
        // return Inertia::render('Dashboard', [
        //     'users' => $allUsers,
        //     'selected_user' => $selectedUser
        // ]);
        // return redirect('/dashboard');
    }
    public function getAllUsers()
    {
        // Get all users where finished is false
        $availableUsers = User::where('finished', false)->get();

        if ($availableUsers->isEmpty()) {
            // If no users are available (all finished), reset all users and start over
            User::query()->update([
                'finished' => false,
                'selected' => false
            ]);
            
            // Get all users again
            $availableUsers = User::all();
        }

        // Randomly select one user from available users
        $selectedUser = $availableUsers->first();
        
        // Set the selected user as selected
        $selectedUser->update(['selected' => true]);

        // Return all users
        $allUsers = User::all();

        return response()->json([
            'success' => true,
            'users' => $allUsers,
            'selected_user' => $selectedUser
        ]);
    }
    public function toggleSelected($id)
    {
        $user = User::find($id);
        $user->selected = !$user->selected;
        $user->save();

        
        // Return all users
        $allUsers = User::all();

        return response()->json([
            'success' => true,
            'users' => $allUsers,
            'selected_user' => $user
        ]);
    }

     public function toggleFinished($id)
    {
        $user = User::find($id);
        if (!$user->finished){
            $user->count =  $user->count + 1;
        }
        $user->finished =  !$user->finished;
        $user->save();

        
        // Return all users
        $allUsers = User::all();

        return response()->json([
            'success' => true,
            'users' => $allUsers
        ]);
    }
}