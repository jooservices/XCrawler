<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Modules\Client\Http\Controllers\OAuthController;

\Illuminate\Support\Facades\Route::prefix('client')
    ->name('client.')
    ->group(function () {
        Route::prefix('oauth')
        ->name('oauth.')
        ->group(function () {
            \Illuminate\Support\Facades\Route::get('/google/callback', [OAuthController::class,'google'])
            ->name('google');
        });
    });
