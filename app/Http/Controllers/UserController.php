<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bean;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Features;


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
            'finished' => false
        ]);

        // Get all users where finished is false
        $availableUsers = User::all();

        // Randomly select one user from available users => changed to one with highest drank
        $selectedUser = $availableUsers->sortByDesc('drank')->first();
        $total = $selectedUser->total + $selectedUser->drank;
        $selectedUser->update(['selected' => true, "total" => $total, "drank" => 0]);

        if($selectedUser->id == 1){
            $this->sendPush("13");
        }else if (selectedUser->id == 2){
            $this->sendPush("14");
        }else if (selectedUser->id == 3){
            $this->sendPush("15");
        }else if (selectedUser->id == 4){
            $this->sendPush("16");
        }else if (selectedUser->id == 5){
            $this->sendPush("17");
        }else if (selectedUser->id == 6){
            $this->sendPush("18");
        }else if (selectedUser->id == 7){
            $this->sendPush("19");
        }


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

    function sendPush($id){
       $response = Http::withHeaders([
                'accept' => 'application/json',
                'X-API-Key' => '2zMDIIrs4vQBhYSb1HwiQbIzLPRQv55E0XkxWzPd7BBZNKDq'
            ])
            ->withoutVerifying()
            ->get('https://10.32.244.187:8089/api-admin/api/v1/resources/1/io-configs/' . $id . '/set/true');
        return $response;
    }

    public function getAllUsers()
    {

        $selectedUser = User::where('selected', true)->first();

        $availableUsers = User::all();
      
        if(!$selectedUser){
            $selectedUser = $availableUsers->sortByDesc('drank')->first();
            $total = $selectedUser->total + $selectedUser->drank;
            $selectedUser->update(['selected' => true, "total" => $total, "drank" => 0]);
        }

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
        $user->count =  $user->count + 1;
        
        // Return all users
        $allUsers = User::all();

        return response()->json([
            'success' => true,
            'users' => $allUsers
        ]);
    }

     public function addDrank($id)
    {
        $user = User::find($id);
        $user->drank =  $user->drank + 1;
        $user->save();

        $currentBeans = Bean::where("finished", false)->first();
        $currentBeans->count = $currentBeans->count + 1; 
        $currentBeans->save();

        $allUsers = User::all();

        return response()->json([
            'success' => true,
            'users' => $allUsers
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
                'message' => 'Login successful'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login'
            ], 500);
        }
    }

}