<?php

use App\Modules\Core\Http\Controllers\FileController;
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

Route::prefix('files')
    ->name('files.')
    ->controller(FileController::class)
    ->group(function () {
        Route::post('/', 'create')->name('create');
    });
