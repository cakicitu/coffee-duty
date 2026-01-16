<?php

namespace App\Http\Controllers;

use App\Models\Bean;
use Illuminate\Http\Request;

class BeanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $beans = Bean::orderBy('id', 'desc')->get();
        $currentBeans = Bean::where("finished", false)->first();

        return ["beans" => $beans, "currentBeans" => $currentBeans];
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

        $beans = Bean::orderBy('id', 'desc')->get();
        $currentBeans = Bean::where("finished", false)->first();

        return ["beans" => $beans, "currentBeans" => $currentBeans];
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
