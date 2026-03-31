<?php

use Illuminate\Support\Facades\Route;
use Modules\Staff\Http\Controllers\Api\v1\SetupPasswordController;
use Modules\Staff\Http\Controllers\Api\v1\StaffLoginController;
use Modules\Staff\Http\Controllers\Api\v1\StaffLogoutController;

Route::prefix('staff')->group(function () {
    // Public auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', StaffLoginController::class)->name('staff.auth.login');

        // Protected auth routes (require tenant authentication)
        Route::middleware('auth:tenant')->group(function () {
            Route::post('/logout', StaffLogoutController::class)->name('staff.auth.logout');
        });
    });

    // Public setup password route (outside auth prefix)
    Route::post('/setup-password', SetupPasswordController::class)->name('staff.setup-password');
});
