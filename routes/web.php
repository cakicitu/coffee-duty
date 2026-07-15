<?php

use App\Models\Bean;
use App\Models\Dislike;
use App\Models\Like;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {

    $users = User::all();

    return Inertia::render('Dashboard', [
        'users' => $users,
    ]);

})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('beans', function () {

    $beans = Bean::with('likes', 'dislikes')->orderBy('id', 'desc')->get();
    $currentBeans = Bean::where('finished', false)->first();

    $hasEval = false;
    if ($currentBeans) {
        $myLike = Like::where('bean_id', $currentBeans->id)->where('user_id', auth()->user()->id)->first();
        $myDislike = Dislike::where('bean_id', $currentBeans->id)->where('user_id', auth()->user()->id)->first();
        $hasEval = ($myLike || $myDislike);
    }

    return Inertia::render('Beans', [
        'hasEval' => $hasEval,
        'currentBeans' => $currentBeans,
        'beans' => $beans,
    ]);

})->middleware(['auth', 'verified'])->name('beans');

Route::get('cleaning', [App\Http\Controllers\CleaningController::class, 'page'])
    ->middleware(['auth', 'verified'])->name('cleaning');

Route::get('help', function () {

    return Inertia::render('Help', [
    ]);

})->middleware(['auth', 'verified'])->name('help');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
