<?php

namespace App\Http\Controllers\sizeSet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SizeSetController extends Controller
{
    //
    public function index()
    {
        return view('size-sets.index');
    }
}
