<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{
    public function show()
    {
        $user = getUser(auth()->user()->id);

        return response()->json(['data'=>$user], 200);
    }

    public function getUserByUsername(Request $request, $username){
        $user = User::select('id', 'name', 'username', 'profile_picture')
                    ->where('username', 'LIKE', '%'.$username.'%')
                    ->where('id', '<>', auth()->user()->id)
                    ->get();

        $user->map(function ($item){
            $item->profile_picture = $item->profile_picture ?
                url('storage/'.$item->profile_picture) : '';

            return $item;
        });

        return response()->json(['data'=>$user], 200);
    }

    public function update(Request $request){
        try {
            $user = User::find(auth()->user()->id);

            $data = $request->only('name', 'username', 'email', 'password');

            if($request->username != $user->username){
                $isExistUsername = User::where('username', $request->username)->exists();

                if($isExistUsername){
                    return response()->json(['message' => 'Username already taken'], 409);
                }
            }

            if($request->email != $user->email){
                $isExistEmail = User::where('email', $request->email)->exists();

                if($isExistEmail){
                    return response()->json(['message' => 'Email already taken'], 409);
                }
            }

            if($request->password){
                $data['password'] = bcrypt($request->password);
            }

            if($request->profile_picture){
                $profilePicture = uploadBase64Image($request->profile_picture);
                $data['profile_picture'] = $profilePicture;

                if($user->profile_picure){
                    Storage::delete('public/'.$user->profile_picture);
                }
            }

            $user->update($data);

            return response()->json(['data' => $data], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function isEmailExist(Request $request){
        $validator = Validator::make($request->only('email'), [
            'email' => 'required|email'
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        $isExist = User::where('email', $request->email)->exists();

        return response()->json(['is_email_exist' => $isExist]);
    }


}
