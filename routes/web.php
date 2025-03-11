<?php

use Illuminate\Support\Facades\Route; 

Route::get('/', function () {
    return view('layouts.app', ['component' => 'home-page']);
});
Route::get('/products', function () {
    return view('layouts.app', ['component' => 'product-details-page']);
});