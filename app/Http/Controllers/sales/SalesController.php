<?php

namespace App\Http\Controllers\sales;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Helpers\NumeroALetras;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;

class SalesController extends Controller
{
    #[Middleware('auth')]
    public function index()
    {
        return view('sale.index');
    }

    public function show(Voucher $voucher)
    {
        $voucher->load([
            'businessLocation.business',
            'customer',
            'items.variant.product',
            'items.variant.size',
            'payments',
        ]);

        $montoEnLetras = NumeroALetras::convertir((float) $voucher->total);

        //dd($voucher);
        return view('sale.print', compact('voucher', 'montoEnLetras'));
    }
}
