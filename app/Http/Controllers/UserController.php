<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function login(Request $request){
        $credentials = $request->only('email', 'password');

        if(Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('BasApp')->plainTextToken;
            return response()->json(['token' => $token], 200);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}
