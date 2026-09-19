<?php

namespace App\Http\Controllers\sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;

class SalesController extends Controller
{
    #[Middleware('auth')]
    public function index()
    {
        return view('sale.index');
    }
}
