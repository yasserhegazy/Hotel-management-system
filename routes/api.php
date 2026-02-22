<?php

/**
 * API Routes
 * These routes are loaded by the RouteServiceProvider and assigned the "api" middleware group.
 * They are prefixed with "/api" by default.
 */

// Load module API routes
foreach (glob(__DIR__.'/../modules/*/Routes/api.php') as $routeFile) {
    require $routeFile;
}
