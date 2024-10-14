<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    
    public function createGame($id)
    {
        $player = User::find($id);
        if (Auth::user()->id != $id) {
            return response()->json(['message' => 'No estás autorizado para crear juegos para este usuario'], 403);
        }
            $dice1 = rand(1, 6);
            $dice2 = rand(1, 6);
            $is_win = ($dice1 + $dice2) === 7;

            $game = $player->games()->create([
                'dice1' => $dice1,
                'dice2' => $dice2,
                'winner' => $is_win,
            ]);

        return response()->json($game);
    }
    public function deleteGame($id)
    {
        $player = User::find($id);

        if (Auth::user()->id != $id) {
            return response()->json(['message' => 'No estás autorizado para borrar juegos para este usuario'], 404);
        }

        $player->games()->delete();

        return response()->json(['message' => 'Tiradas eliminadas'], 200);
    }
}
