<?php

namespace App\Http\Controllers;
use App\Models\User;

use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function register(Request $request){
        $request->validate(
            [
                'name' => ['nullable', 'string', 'max:100'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string' ]
            ]
        );
        $user = User::create(
            [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]
        );
        $token=$user->createToken('auth_token')->accessToken;

        return response()->json([
            'token' => $token,
            'message' => 'Acabas de registrarte!!!',
        ], 201);
    }
    public function login(Request $request){
        $request->validate(
            [
                'email' => ['required'],
                'password' => ['required']
            ]
        );
        $user=User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            return response([
                'error'=>'Los datos introducidos no son correctos',
                'success' => false,
            ],401);
        }
        
        $token=$user->createToken('auth_token')->accessToken;

        return response([
            'token'=> $token,
            'message'=>'Estás en sesión'
        ]);
    }
    public function getUser($id){
        $user = User::with('games')->find($id);
        if ($user->games->isEmpty()) {
            return response()->json(['message' => 'Este usuario no ha hecho ninguna jugada.'], 200);
        }else{
            return response()->json($user->games);
        }

    }
    public function logout(Request $request){
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'Has salido del juego'
        ], 200);
    }
    public function DisplayAllPlayers(Request $request)
    {
        /*$roles = $request->user()->getRoleNames();
        return response()->json($roles);*/
        if ($request->user()->hasRole('admin')) {
            $users = User::all();
            return response()->json($users);
        } else {
            return response()->json([
                'message' => 'No tienes permiso para acceder a esta información.'
            ], 403);
        }
    }
    public function updateUser(Request $request, string $id){
        $user = User::find($id);
        if ($request->user()->id !== $user->id) {

            abort(403, 'No puedes cambiar el nickname de otro usuario.');
        }else
         {
            if(empty($request->name)){
                $name = $user->name;
                return response()->json([
                    'message' => $name.' , no puedes dejar vacío este campo'
                ], 400);
            }else{
                $request->validate(['name' => ['nullable', 'string', 'max:100']]);
                $user->name = $request->name;
                $user->save();
            }
            
        }

        return response()->json(['message' => 'Nickname modificado.'], 200);

    }
    public function userRanking(Request $request, $returnData = false){
        //dd($request->user()->rol);
        if ($request->user()->rol === 'admin') {
            $users = User::with('games')->get();

            $users = $users->map(function ($user) {
            $totalGames = $user->games->count();
            $winGames = $user->games->where('winner', true)->count();
            $successRate = $totalGames > 0 ? ($winGames / $totalGames) * 100 : 0;
            $user->success_rate = $successRate; 
            $user->save(); 
            return $user;
            });
            if ($returnData) {//para reutilizar
                return $users;
            }
            $users = $users->sortByDesc('success_rate')->values();

  
            return response()->json([$users], 200);
        }else{
           
                return response()->json([
                    'message' => 'No tienes permiso para acceder a esta información.'
                ], 403);
            
        }
        
    }
    public function getLoser() {
    
        $players = User::where('rol', 'jugador')->orderBy('success_rate')->get();
    
        if ($players->isEmpty()) {
            return response()->json([
                'message' => 'No hay jugadores disponibles para determinar un perdedor.'
            ], 404);
        }
    
        $loser = $players->first();
    
        return response()->json([
            'loser' => $loser->name,
            'success_rate' => $loser->success_rate,
            'message' => 'Este es el jugador con el peor porcentaje de éxito'
        ], 200);
    }
    
    public function getWin(){
        
        $players = User::where('rol', 'jugador')->orderByDesc('success_rate')->get();

        if ($players->isEmpty()) {
            return response()->json([
                'message' => 'No hay jugadores disponibles para determinar un ganador.'
            ], 404);
        }
    
        $winner = $players->first();
    
        return response()->json([
            'winner' => $winner->name,
            'success_rate' => $winner->success_rate,
            'message' => 'Este es el jugador con el mejor porcentaje de éxito'
        ], 200);
    }
  

}
