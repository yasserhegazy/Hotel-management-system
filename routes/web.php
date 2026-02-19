<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Modules\Auth\Http\Controllers\Api\v1\RegisterController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');
Route::prefix('auth')->group(function () {
    Route::post('/register', RegisterController::class);
});
