<?php

namespace App\Http\Controllers\equipo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    //
    public function index()
    {
        return view('equipo.index');
    }
}
