<?php

use App\Modules\JAV\Http\Controllers\JAVController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('jav')
    ->name('jav.')
    ->group(function () {
        Route::get('/', [JAVController::class, 'index'])->name('index');
    });
