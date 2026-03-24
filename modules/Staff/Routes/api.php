<?php

use Illuminate\Http\Request;
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
use Modules\Staff\Http\Controllers\Api\v1\ValidateSetupTokenController;
use Modules\Staff\Http\Middleware\EnsureCanManageStaff;
use Modules\Staff\Http\Middleware\InitializeTenancyForStaff;
use Modules\Staff\Http\Resources\StaffUserResource;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;

Route::prefix('staff')->group(function () {
    // Auth routes — tenancy initialized via X-Tenant header
    Route::prefix('auth')->group(function () {
        Route::post('/login', StaffLoginController::class)
            ->middleware(InitializeTenancyByRequestData::class)
            ->name('staff.auth.login');

        Route::middleware([InitializeTenancyByRequestData::class, 'auth:tenant'])->group(function () {
            Route::post('/logout', StaffLogoutController::class)->name('staff.auth.logout');

            Route::get('/me', function (Request $request) {
                $user = $request->user('tenant');

                return response()->json(
                    new StaffUserResource($user)
                );
            })->name('staff.auth.me');
        });
    });

    // Setup password — tenancy initialized via X-Tenant header
    Route::post('/setup-password', SetupPasswordController::class)
        ->middleware(InitializeTenancyByRequestData::class)
        ->name('staff.setup-password');

    Route::get('/validate-setup-token', ValidateSetupTokenController::class)
        ->middleware(InitializeTenancyByRequestData::class)
        ->name('staff.validate-setup-token');

    // Staff management routes (owner via sanctum OR staff with manage_staff permission)
    Route::middleware([InitializeTenancyByRequestData::class, 'auth:sanctum,tenant', EnsureCanManageStaff::class, InitializeTenancyForStaff::class])->group(function () {
        Route::get('/', ListStaffController::class)->name('staff.index');
        Route::post('/', CreateStaffController::class)->name('staff.store');
        Route::get('/{staff_id}', ShowStaffController::class)->name('staff.show');
        Route::patch('/{staff_id}', UpdateStaffController::class)->name('staff.update');
        Route::delete('/{staff_id}', DeactivateStaffController::class)->name('staff.deactivate');
        Route::post('/{staff_id}/resend-setup', ResendStaffSetupController::class)->name('staff.resend-setup');
    });
});
