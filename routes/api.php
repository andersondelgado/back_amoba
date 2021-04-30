<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/fs/{filename}',[UserController::class,'getPubliclyStorageFile']);
Route::middleware(['custom-client'])->group(function () {
    Route::prefix('/users')->group(function () {
        Route::get('/{id?}', [UserController::class, 'index']);
        Route::post('/{id?}', [UserController::class, 'store']);
        Route::delete('/{id}', [UserController::class, 'delete']);
    });
});
