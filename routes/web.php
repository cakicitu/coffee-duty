<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Like;
use App\Models\Dislike;
use App\Models\Bean;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('dashboard', function () {

        $selectedUser = User::where('selected', true)->first();

        $availableUsers = User::all();

        if(!$selectedUser){
            $selectedUser = $availableUsers->sortByDesc('drank')->first();
            $total = $selectedUser->total + $selectedUser->drank;
            $selectedUser->update(['selected' => true, "total" => $total, "drank" => 0]);
        }


        $users = User::all();

        return Inertia::render('Dashboard', [
            'users' => $users
        ]);
        
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('beans', function () {

        $beans = Bean::with('likes', 'dislikes')->orderBy('id', 'desc')->get();
        $currentBeans = Bean::where("finished", false)->first();
        $myLike = Like::where("bean_id", $currentBeans->id)->where("user_id", auth()->user()->id)->first();
        $myDislike = Dislike::where("bean_id", $currentBeans->id)->where("user_id", auth()->user()->id)->first();

        $hasEval = ($myLike ||  $myDislike);

        return Inertia::render('Beans', [
            'hasEval' => $hasEval,
            'currentBeans' => $currentBeans,
            'beans' => $beans
        ]);
        
})->middleware(['auth', 'verified'])->name('beans');

Route::get('help', function () {

        return Inertia::render('Help', [
        ]);
        
})->middleware(['auth', 'verified'])->name('help');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
