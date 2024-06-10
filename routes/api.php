<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BatokController;
use App\Http\Controllers\Api\BahanBakuController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AyakManualController;
use App\Http\Controllers\Api\AyakRotariController;
use App\Http\Controllers\Api\DiskmillController;
use App\Http\Controllers\Api\MixingController;
use App\Http\Controllers\Api\BriketController;
use App\Http\Controllers\Api\OvenController;
use App\Http\Controllers\Api\SumberBatokController;

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

    // SUMBER BATOK
    Route::get('sumber_batok', [SumberBatokController::class, 'index']);
    Route::post('store/sumber_batok', [SumberBatokController::class, 'store']);

    // BATOK
    Route::get('batok', [BatokController::class, 'index']);
    Route::post('store/batok', [BatokController::class, 'store']);
    Route::post('update/batok/{id}', [BatokController::class, 'update']);
    Route::delete('delete/batok/{id}', [BatokController::class, 'delete']);
    Route::get('batok/{id}', [BatokController::class, 'show']);
    Route::get('batok/resource/{resource}', [BatokController::class, 'showByResource']);

    // BAHAN BAKU
    Route::get('bahan_baku', [BahanBakuController::class, 'index']);
    Route::post('store/bahan_baku', [BahanBakuController::class, 'store']);
    Route::post('update/bahan_baku/{id}', [BahanBakuController::class, 'update']);
    Route::delete('delete/bahan_baku/{id}', [BahanBakuController::class, 'delete']);
    Route::get('bahan_baku/{id}', [BahanBakuController::class, 'show']);

    // AYAK MANUAL
    Route::get('ayak_manual', [AyakManualController::class, 'index']);
    Route::post('store/ayak_manual', [AyakManualController::class, 'store']);
    Route::post('update/ayak_manual/{id}', [AyakManualController::class, 'update']);
    Route::delete('delete/ayak_manual/{id}', [AyakManualController::class, 'delete']);
    Route::get('ayak_manual/{id}', [AyakManualController::class, 'show']);

    // AYAK ROTARI
    Route::get('ayak_rotari', [AyakRotariController::class, 'index']);
    Route::post('store/ayak_rotari', [AyakRotariController::class, 'store']);
    Route::post('update/ayak_rotari/{id}', [AyakRotariController::class, 'update']);
    Route::delete('delete/ayak_rotari/{id}', [AyakRotariController::class, 'delete']);
    Route::get('ayak_rotari/{id}', [AyakRotariController::class, 'show']);

    // DISKMILL
    Route::get('diskmill', [DiskmillController::class, 'index']);
    Route::post('store/diskmill', [DiskmillController::class, 'store']);
    Route::post('update/diskmill/{id}', [DiskmillController::class, 'update']);
    Route::delete('delete/diskmill/{id}', [DiskmillController::class, 'delete']);
    Route::get('diskmill/{id}', [DiskmillController::class, 'show']);

    // MIXING
    Route::get('mixing', [MixingController::class, 'index']);
    Route::post('store/mixing', [MixingController::class, 'store']);
    Route::post('update/mixing/{id}', [MixingController::class, 'update']);
    Route::delete('delete/mixing/{id}', [MixingController::class, 'delete']);
    Route::get('mixing/{id}', [MixingController::class, 'show']);

    // OVEN
    Route::get('oven', [OvenController::class, 'index']);
    Route::post('store/oven', [OvenController::class, 'store']);
    Route::post('update/oven/{id}', [OvenController::class, 'update']);
    Route::delete('delete/oven/{id}', [OvenController::class, 'delete']);
    Route::get('oven/{id}', [OvenController::class, 'show']);

    // BRIKET
    Route::get('briket', [BriketController::class, 'index']);
    Route::post('store/briket', [BriketController::class, 'store']);
    Route::post('update/briket/{id}', [BriketController::class, 'update']);
    Route::delete('delete/briket/{id}', [BriketController::class, 'delete']);
    Route::get('briket/{id}', [BriketController::class, 'show']);
});
