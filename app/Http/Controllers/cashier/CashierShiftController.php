<?php

namespace App\Http\Controllers\cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;

class CashierShiftController extends Controller
{
    
    #[Middleware('auth')]
    public function index()
    {
        return view('cashier.index');
    }
}
