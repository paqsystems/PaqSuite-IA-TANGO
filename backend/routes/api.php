<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.v1.auth.forgotPassword');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('api.v1.auth.resetPassword');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        Route::post('/auth/change-password', [AuthController::class, 'changePassword'])->name('api.v1.auth.changePassword');

        Route::get('/user', function (Request $request) {
            return $request->user();
        })->name('api.v1.user');

        Route::get('/user/profile', [UserProfileController::class, 'show'])->name('api.v1.user.profile');
        Route::put('/user/profile', [UserProfileController::class, 'update'])->name('api.v1.user.profile.update');
    });
});
