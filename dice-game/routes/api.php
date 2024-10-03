<?php

use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');*/
Route::post('/players', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

Route::group(['middleware' => 'auth:api', 'role:admin'], function (){
    //mostramos todos los jugadores
    Route::get('/players',[UserController::class, 'DisplayAllPlayers']);
});
Route::group(['middleware' => 'auth:api'], function (){
    Route::post('/players/{id}/games', [GameController::class, 'createGame']); 
    Route::delete('/players/{id}/games', [GameController::class, 'deleteGames']);
    Route::put('/players/{id} ', [UserController::class, 'updateUser']); 
});