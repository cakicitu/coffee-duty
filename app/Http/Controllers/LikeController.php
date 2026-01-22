<?php

namespace App\Http\Controllers;

use App\Models\Bean;
use Illuminate\Http\Request;
use App\Models\Like;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $user = $request->user();
        $likeVal = $request->get('like');
        $beanId = $request->get('beanId');

        $like = Like::create([
            'user_id' => $user->id,
            'bean_id' => $beanId,
        ]);

        $beans = Bean::orderBy('id', 'desc')->get();
        $currentBeans = Bean::where("finished", false)->first();

        return ["like" => $like, "currentBeans" => $currentBeans, "beans" => $beans];
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
