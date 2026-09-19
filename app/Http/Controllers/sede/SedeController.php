<?php

namespace App\Http\Controllers\sede;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SedeController extends Controller
{
    //
    public function index()
    {
        return view('sedes.index');
    }
}
