<?php

namespace App\Http\Controllers\cambio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CambioController extends Controller
{
    //
    public function index()
    {
        return view('cambio.index');
    }
}
