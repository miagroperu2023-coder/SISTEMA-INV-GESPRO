<?php

namespace App\Http\Controllers\investment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InvestmentController extends Controller
{
    //
    public function index()
    {
        return view('investment.index');
    }
}
