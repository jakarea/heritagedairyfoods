<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController; 

Route::get('/', function () {
    return view('layouts.app', ['component' => 'home-page']);
});

Route::get('/product/{slug}', [ProductController::class, 'productDetails']); 