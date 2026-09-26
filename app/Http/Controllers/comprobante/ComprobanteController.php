<?php

namespace App\Http\Controllers\comprobante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComprobanteController extends Controller
{
    //
    public function index()
    {
        return view('comprobante.index');
    }
}
