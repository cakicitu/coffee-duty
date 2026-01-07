<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Bean;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('dashboard', function () {

        $selectedUser = User::where('selected', true)->first();

        $availableUsers = User::where('finished', false)->get();
        if ($availableUsers->isEmpty()) {
            User::query()->update([
                'finished' => false,
                'selected' => false
            ]);
            
            $availableUsers = User::all();
        }
      
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

        $beans = Bean::orderBy('id', 'desc')->get();
        $currentBeans = Bean::where("finished", false)->first();

        return Inertia::render('Beans', [
            'currentBeans' => $currentBeans,
            'beans' => $beans
        ]);
        
})->middleware(['auth', 'verified'])->name('beans');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
