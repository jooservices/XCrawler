<?php

use Illuminate\Support\Facades\Route;
use Modules\Flick\Http\Controllers\FlickController;
use Modules\Flick\Http\Controllers\FlickCallbackController;

// Callback route (no auth - FlickrHub needs to access this)
Route::post('flick/callback', \Modules\Flick\Http\Controllers\FlickCallbackController::class);

Route::prefix('flick')->group(function () {
    Route::get('stats', [\Modules\Flick\Http\Controllers\DashboardController::class, 'stats']);
    Route::get('tasks', [\Modules\Flick\Http\Controllers\DashboardController::class, 'tasks']);
    Route::get('contacts', [\Modules\Flick\Http\Controllers\DashboardController::class, 'contacts']);
    Route::get('photos', [\Modules\Flick\Http\Controllers\DashboardController::class, 'photos']);
    Route::post('commands', [\Modules\Flick\Http\Controllers\DashboardController::class, 'execute']);
});

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('flicks', FlickController::class)->names('flick');
});
