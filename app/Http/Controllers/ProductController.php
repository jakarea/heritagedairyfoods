<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    //

    public function productDetails($slug)
    {
        return view('layouts.app', ['component' => 'product-details-page', 'productSlug' => $slug]);
    }
}