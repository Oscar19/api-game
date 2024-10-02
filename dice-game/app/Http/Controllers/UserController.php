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
                'name' => $request->nickname,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]
        );
        $token=$user->createToken('auth_token')->accessToken;

        return response([
            'token'=> $token,
            'message'=>'Acabas de registrarte!!!',
        ]);
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
                'message'=>'Los datos introducidos no son correctos'
            ]);
        }
        
        $token=$user->createToken('auth_token')->accessToken;

        return response([
            'token'=> $token,
            'message'=>'Estás en sesión'
        ]);
    }

}
