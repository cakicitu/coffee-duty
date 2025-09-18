<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('dashboard', function () {

        $selectedUser = User::where('selected', true)->get();

        if(!$selectedUser){
            $availableUsers = User::where('finished', false)->get();
            $selectedUser = $availableUsers->first();
            $selectedUser->update(['selected' => true]);
        }

        $users = User::all();

        return Inertia::render('Dashboard', [
            'users' => $users
        ]);
        
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
