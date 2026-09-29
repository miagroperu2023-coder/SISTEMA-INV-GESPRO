<?php

namespace App\Http\Controllers\import;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImportExcelController extends Controller
{
    //
    public function index()
    {
        return view('import.index');
    }
}
