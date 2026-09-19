<?php

namespace App\Http\Controllers\product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;

class ProductController extends Controller
{
    //#[Middleware('auth')]
    public function index()
    {
        return view('product.index');
    }
}
