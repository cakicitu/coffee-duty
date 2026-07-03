<?php

namespace App\Http\Controllers;

use App\Models\Bean;
use App\Models\User;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;

class BeanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $beans = Bean::with('likes', 'dislikes')->orderBy('id', 'desc')->get();
        $currentBeans = Bean::where("finished", false)->first();


        return [
            'currentBeans' => $currentBeans,
            'beans' => $beans
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $oldBeans = Bean::where("finished", false)->first();

        if ($oldBeans){
            $oldBeans->finished = true;
            $oldBeans->finished_at = now();
            $oldBeans->save();
        }

        // Handle who actually brought the coffee, if provided.
        $bringerId = $request->input('user');
        if ($bringerId) {
            $selectedUser = User::where('selected', true)->first();

            if ($selectedUser && $selectedUser->id == $bringerId) {
                // The correct user brought it, so run the normal rotation ("I got the coffee").
                app(UserController::class)->selectJob();
            } else {
                // A different user brought it, so bank their drank into total and reset it.
                $bringer = User::find($bringerId);
                if ($bringer) {
                    $bringer->count = $bringer->count + 1;
                    $bringer->total = $bringer->total + $bringer->drank;
                    $bringer->drank = 0;
                    $bringer->save();
                }
            }
        }

        Bean::create();

        $beans = Bean::with('likes', 'dislikes')->orderBy('id', 'desc')->get();
        $currentBeans = Bean::where("finished", false)->first();

        return [
            'currentBeans' => $currentBeans,
            'beans' => $beans
        ];
    }
}
