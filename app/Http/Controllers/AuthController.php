<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function generateToken($token){
        return response()->json([
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60,
        'user' => auth()->setToken($token)->user(),
        ]);
    }
    public function register(Request $request){
        $request->validate([
            "name"=>"required|string|max:255",
            "email"=>"required|email|string|unique:users",
            "password"=>"required|string|min:8|confirmed"
        ]);

        $user=User::create([
            "first_name"=>$request->name,
            "email"=>$request->email,
            "password"=>Hash::make($request->password)
        ]);

        $token = JWTAuth::fromUser($user);
        return $this->generateToken($token);

    }

    
}
