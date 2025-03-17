<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Mpdf\Mpdf;

class ProductController extends Controller
{
    //

    public function productDetails($slug)
    {
        return view('layouts.app', ['component' => 'product-details-page', 'productSlug' => $slug]);
    } 
}
