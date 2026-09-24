<?php

namespace App\Http\Controllers\serie;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SerieController extends Controller
{
    //
    public function index()
    {
        return view('serie.index');
    }
}
