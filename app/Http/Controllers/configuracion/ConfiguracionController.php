<?php

namespace App\Http\Controllers\configuracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    //
    public function index()
    {
        return view('configuracion.index');
    }
}
