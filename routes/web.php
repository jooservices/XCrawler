<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/flickr/dashboard', function () {
    return view('flick::dashboard');
});
