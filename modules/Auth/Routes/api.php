<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\v1\RegisterController;

Route::prefix('auth')->group(function () {
    Route::post('/register', RegisterController::class);
});
