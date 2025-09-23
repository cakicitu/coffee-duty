<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth');

Route::get('/user/all', [UserController::class, 'getAllUsers']);
Route::post('/user/job/select', [UserController::class, 'selectJob']);
Route::post('/user/{id}/toggle/finished', [UserController::class, 'toggleFinished']);
Route::post('/user/{id}/toggle/selected', [UserController::class, 'toggleSelected']);