<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\v1\LoginController;
use Modules\Auth\Http\Controllers\Api\v1\LogoutController;
use Modules\Auth\Http\Controllers\Api\v1\RegisterController;

Route::prefix('auth')->group(function () {
    Route::post('/register', RegisterController::class)->name('auth.register');

    Route::post('/login', LoginController::class)->name('auth.login');

    Route::post('/forgot-password',ForgotPasswordController::class)->name('auth.forgot-password');
    Route::post('/reset-password',ResetPasswordController::class)->name('auth.reset-password');

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', LogoutController::class)->name('auth.logout');

        // Provided to satisfy the 'prevents access to protected routes after logout' test
        Route::get('/me', function (Request $request) {
            return $request->user();
        })->name('auth.me');
    });
});
