<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Melihovv\Base64ImageDecoder\Base64ImageDecoder;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request){

        // Retrive all data from body
        $data = $request->all();

        // Validation process
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email',
            'password'=> 'required|string|min:6',
            // 'pin'=> 'required|digits:6'
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        $user = User::where('email', $request->email)->exists();

        if($user){
            return response()->json(['message' => 'Email already taken'], 409);
        }

        DB::beginTransaction();

        try {
            $profilePicture = null;

            if($request->profile_picture){
                $profilePicture = $this->uploadBase64Image($request->profile_picture);
            }

            $user = User::create([
                'name'=> $request->name,
                'email'=> $request->email,
                'username'=> $request->username,
                'password'=> bcrypt($request->password),
                'pin' => $request->pin,
                'position' => $request->position,
                'phone'=> $request->phone,
                'profile_picture'=> $profilePicture,
            ]);

            DB::commit();

            $token = JWTAuth::attempt(['email' => $request->email, 'password' => $request->password]);

            $userResponse = getUser($request->email);
            $userResponse->token = $token;
            $userResponse->token_expires_in = auth()->factory()->getTTL() * 60;
            $userResponse->token_type = 'bearer';

            return response()->json(['data' => $userResponse], 200);

        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function login(Request $request){

        $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        try {
            $token = JWTAuth::attempt($credentials);

            if(!$token) {
                return response()->json(['message' => 'Login credentials are invalid']);
            }

            $userResponse = getUser($request->email);
            $userResponse->token = $token;
            $userResponse->token_expires_in = auth()->factory()->getTTL() * 60;
            $userResponse->token_type = 'bearer';

            return response()->json(['data' => $userResponse], 200);

        } catch (\JWTException $th) {
            return respose()->json(['message' => $th->getMessage()], 500);
        }

    }

    private function uploadBase64Image($base64Image){
        $decoder = new Base64ImageDecoder($base64Image, $allowedFormats = ['jpeg', 'png', 'gif']);

        $decodedContent = $decoder->getDecodedContent();
        $format = $decoder->getFormat();
        $image = Str::random(10).'.'.$format;
        Storage::disk('public')->put($image, $decodedContent);

        return $image;
    }

    public function logout(){
        auth()->logout();

        return response()->json(['message' => 'Logout success'], 200);
    }

}
