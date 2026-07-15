<?php

namespace App\Http\Controllers;

use App\Models\Bean;
use App\Models\Dislike;
use App\Models\Like;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'beanId' => 'required|integer|exists:beans,id',
        ]);

        $user = $request->user();

        // A user has one evaluation per bean, so liking replaces any dislike
        Dislike::where('user_id', $user->id)->where('bean_id', $validated['beanId'])->delete();

        $like = Like::firstOrCreate([
            'user_id' => $user->id,
            'bean_id' => $validated['beanId'],
        ]);

        $beans = Bean::orderBy('id', 'desc')->get();
        $currentBeans = Bean::where('finished', false)->first();

        return ['like' => $like, 'currentBeans' => $currentBeans, 'beans' => $beans];
    }

    /**
     * Display the specified resource.
     */
    public function show(Bean $bean)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bean $bean)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bean $bean)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bean $bean)
    {
        //
    }
}
