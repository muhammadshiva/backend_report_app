<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BatokController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//
// Route::middleware('jwt.verify')->get('test', function (Request $request) {
//     return 'success';
// });

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('is_email_exist', [UserController::class, 'isEmailExist']);

Route::group(['middleware' => 'jwt.verify'], function ($router) {

    // USER
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('users', [UserController::class, 'show']);
    Route::get('users/{username}', [UserController::class, 'getUserByUsername']);
    Route::put('users', [UserController::class, 'update']);

    // BATOK
    Route::post('store/batok', [BatokController::class, 'store']);
    Route::put('update/batok/{id}', [BatokController::class, 'update']);
    Route::delete('delete/batok/{id}', [BatokController::class, 'delete']);
    Route::get('batok', [BatokController::class, 'show']);
});
