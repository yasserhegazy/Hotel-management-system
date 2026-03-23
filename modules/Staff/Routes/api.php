<?php

use Illuminate\Support\Facades\Route;
use Modules\Staff\Http\Controllers\Api\v1\CreateStaffController;
use Modules\Staff\Http\Controllers\Api\v1\DeactivateStaffController;
use Modules\Staff\Http\Controllers\Api\v1\ListStaffController;
use Modules\Staff\Http\Controllers\Api\v1\ResendStaffSetupController;
use Modules\Staff\Http\Controllers\Api\v1\SetupPasswordController;
use Modules\Staff\Http\Controllers\Api\v1\ShowStaffController;
use Modules\Staff\Http\Controllers\Api\v1\StaffLoginController;
use Modules\Staff\Http\Controllers\Api\v1\StaffLogoutController;
use Modules\Staff\Http\Controllers\Api\v1\UpdateStaffController;
use Modules\Staff\Http\Middleware\EnsureCanManageStaff;

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

    // Staff management routes (owner via sanctum OR staff with manage_staff permission)
    Route::middleware(['auth:sanctum,tenant', EnsureCanManageStaff::class])->group(function () {
        Route::get('/', ListStaffController::class)->name('staff.index');
        Route::post('/', CreateStaffController::class)->name('staff.store');
        Route::get('/{staff_id}', ShowStaffController::class)->name('staff.show');
        Route::patch('/{staff_id}', UpdateStaffController::class)->name('staff.update');
        Route::delete('/{staff_id}', DeactivateStaffController::class)->name('staff.deactivate');
        Route::post('/{staff_id}/resend-setup', ResendStaffSetupController::class)->name('staff.resend-setup');
    });
});
