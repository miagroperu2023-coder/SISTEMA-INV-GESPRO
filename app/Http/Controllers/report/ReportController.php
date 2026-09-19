<?php

namespace App\Http\Controllers\report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    //
    public function index()
    {
        return view('report.index');
    }
}
