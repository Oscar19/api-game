<?php

use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;


Route::post('/players', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

Route::group(['middleware' => 'auth:api', 'role:admin'], function (){
    //mostramos todos los jugadores
    Route::get('/players',[UserController::class, 'DisplayAllPlayers']);
    Route::get('/players/ranking',[UserController::class, 'userRanking']);
    Route::get('/players/ranking/loser',[UserController::class, 'getLoser']);
    Route::get('/players/ranking/winner',[UserController::class, 'getWin']);

    
});
Route::group(['middleware' => 'auth:api'], function (){
    Route::post('/players/{id}/games', [GameController::class, 'createGame']);
    Route::get('/players/{id}/games', [GameController::class, 'getUser']);  
    Route::delete('/players/{id}/games', [GameController::class, 'deleteGame']);
    Route::put('/players/{id} ', [UserController::class, 'updateUser']); 
    Route::post('/logout', [GameController::class, 'logout']); 
    
    
});
