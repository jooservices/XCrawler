<?php

use Illuminate\Support\Facades\Route;
use Modules\Flick\Http\Controllers\FlickController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('flicks', FlickController::class)->names('flick');
});
