<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;

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

    public function register(Request $request){
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'username' => 'required|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = new User([
            'name' => $request->name,
            'email'=> $request->email,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'position' => $request->position,
            'phone'=> $request->phone,
        ]);
        $user->save();

        return response()->json(['message' => 'User registered successfully'], 200);
    }

    public function update(Request $request){
        $user = Auth::user();

        $request->validate([
            'name' => 'string',
            'email' => 'email|unique:users,email,'.$user->id,
            'username' => 'string|unique:users,username,'.$user->id,
            'password' => 'string|min:6',
            'position' => 'string',
            'phone' => 'string',
            'profile_picture' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('username')) {
            $user->username = $request->username;
        }

        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->has('position')) {
            $user->position = $request->position;
        }

        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }

        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $imageName = time().'.'.$image->extension();
            $image->move(public_path('images'), $imageName);
            $user->profile_picture = $imageName;
        }

        $user->save();

        return response()->json(['message' => 'User updated successfully'], 200);
    }

}
