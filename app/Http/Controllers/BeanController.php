<?php

namespace App\Http\Controllers;

use App\Models\Bean;
use App\Models\Like;
use App\Models\Dislike;
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
        $myLike = Like::where("bean_id", $currentBeans->id)->where("user_id", auth()->user()->id)->first();
        $myDislike = Dislike::where("bean_id", $currentBeans->id)->where("user_id", auth()->user()->id)->first();

        $hasEval = ($myLike ||  $myDislike);

        return [
            'hasEval' => $hasEval,
            'currentBeans' => $currentBeans,
            'beans' => $beans
        ];
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
        $oldBeans = Bean::where("finished", false)->first();

        if ($oldBeans){
            $oldBeans->finished = true;
            $oldBeans->finished_at = now();
            $oldBeans->save();
        }

        Bean::create();

        $beans = Bean::with('likes', 'dislikes')->orderBy('id', 'desc')->get();
        $currentBeans = Bean::where("finished", false)->first();
        $myLike = Like::where("bean_id", $currentBeans->id)->where("user_id", auth()->user()->id)->first();
        $myDislike = Dislike::where("bean_id", $currentBeans->id)->where("user_id", auth()->user()->id)->first();

        $hasEval = ($myLike ||  $myDislike);

        return [
            'hasEval' => $hasEval,
            'currentBeans' => $currentBeans,
            'beans' => $beans
        ];
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
