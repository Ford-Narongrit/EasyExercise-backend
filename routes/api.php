<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActivityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($route) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('me', [AuthController::class, 'me']);
});

Route::group(['middleware' => 'api'], function ($route) {
    Route::post('activity', [ActivityController::class, 'store']);
    Route::get('activity', [ActivityController::class, 'index']);
});

