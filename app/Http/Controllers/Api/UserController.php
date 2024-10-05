<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use stdClass;

class UserController extends Controller
{
    public function show()
    {
        $user = getUser(auth()->user()->id);

        return response()->json(['data' => $user], 200);
    }

    public function getAllUser()
    {
        try {
            $user = User::all();
            $statusCode = 200;
            $message = 'Success';
            return response()->json(['status' => $statusCode, 'message' => $message, 'data' => $user], $statusCode);
        } catch (\Throwable $th) {
            $statusCode = 500;
            $message = 'Internal server error';
            return response()->json(['status' => $statusCode, 'message' => $message, 'error' => $th->getMessage()], $statusCode);
        }
    }

    public function getUserByUsername(Request $request, $username)
    {
        $user = User::select('id', 'name', 'username', 'profile_picture')
            ->where('username', 'LIKE', '%' . $username . '%')
            ->where('id', '<>', auth()->user()->id)
            ->get();

        $user->map(function ($item) {
            $item->profile_picture = $item->profile_picture ?
                url('storage/' . $item->profile_picture) : '';

            return $item;
        });

        return response()->json(['data' => $user], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            // Fetch user by ID
            $user = User::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $data = $request->only('name', 'username', 'email', 'password', 'profile_picture', 'pin', 'phone', 'position');

            // Check if username is already taken (and it's not the current user's username)
            if ($request->username != $user->username) {
                $isExistUsername = User::where('username', $request->username)->exists();

                if ($isExistUsername) {
                    return response()->json(['message' => 'Username already taken'], 409);
                }
            }

            // Check if email is already taken (and it's not the current user's email)
            if ($request->email != $user->email) {
                $isExistEmail = User::where('email', $request->email)->exists();

                if ($isExistEmail) {
                    return response()->json(['message' => 'Email already taken'], 409);
                }
            }

            // Hash the password if provided
            if ($request->password) {
                $data['password'] = bcrypt($request->password);
            }

            // Handle profile picture upload if provided
            if ($request->profile_picture) {
                $profilePicture = uploadBase64Image($request->profile_picture);
                $data['profile_picture'] = $profilePicture;

                // Delete the old profile picture if it exists
                if ($user->profile_picture) {
                    Storage::delete('public/' . $user->profile_picture);
                }
            }

            // Update the user data
            $user->update($data);

            // DB::commit();

            // Response with success message
            $statusCode = 200;
            $message = 'Success';
            return response()->json(['status' => $statusCode, 'message' => $message, 'data' => $user], $statusCode);
        } catch (\Throwable $th) {
            // DB::rollback();
            $statusCode = 500;
            $message = 'Internal server error';
            return response()->json(['status' => $statusCode, 'message' => $message, 'error' => $th->getMessage()], $statusCode);
        }
    }

    public function isEmailExist(Request $request)
    {
        $validator = Validator::make($request->only('email'), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        $isExist = User::where('email', $request->email)->exists();

        return response()->json(['is_email_exist' => $isExist]);
    }

    public function deleteUser($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $user->delete();

            DB::commit();

            $statusCode = 200;
            $message = 'User deleted successfully';
            return response()->json(['status' => $statusCode, 'message' => $message, 'data' => new \stdClass()], $statusCode);
        } catch (\Throwable $th) {
            DB::rollback();
            $statusCode = 500;
            $message = 'Internal server error';
            return response()->json(['status' => $statusCode, 'message' => $message, 'error' => $th->getMessage()], $statusCode);
        }
    }
}
