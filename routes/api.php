<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\PinController;
use Illuminate\Support\Facades\Route;

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

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthenticationController::class, 'register']);
    Route::post('login', [AuthenticationController::class, 'login']);

    Route::middleware("auth:sanctum")->group(function () {
        Route::get("user", [AuthenticationController::class, 'user']);
        Route::get('logout', [AuthenticationController::class, 'logout']);
    });
});

Route::middleware("auth:sanctum")->group(function () {
    Route::prefix('onboarding')->group(function () {
        Route::post('setup/pin', [PinController::class, 'setupPin']);
        Route::middleware('has.set.pin')->group(function () {
            Route::post('validate/pin', [PinController::class, 'validatePin']);
        });
    });
});
