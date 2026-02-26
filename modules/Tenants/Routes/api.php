<?php

use Illuminate\Support\Facades\Route;
use Modules\Tenants\Http\Controllers\Api\v1\InitRegistrationController;
use Modules\Tenants\Http\Controllers\Api\v1\SetPasswordController;
use Modules\Tenants\Http\Controllers\Api\v1\TenantController;
use Modules\Tenants\Http\Controllers\Api\v1\VerifyEmailController;

Route::prefix('v1/hotels')->group(function () {
    Route::post('/init-register', InitRegistrationController::class)
        ->name('api.v1.hotels.init-register');

    Route::get('/verify/{token}', VerifyEmailController::class)
        ->name('api.v1.hotels.verify');

    Route::post('/set-password', SetPasswordController::class)
        ->name('api.v1.hotels.set-password');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [TenantController::class, 'show'])
            ->name('api.v1.hotels.profile.show');

        Route::patch('/{hotel_id}', [TenantController::class, 'update'])
            ->name('api.v1.hotels.update');
    });

});
