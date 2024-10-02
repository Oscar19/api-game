<?php

use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');*/
Route::post('/players', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);