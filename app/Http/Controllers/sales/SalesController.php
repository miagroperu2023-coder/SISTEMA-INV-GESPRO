<?php

namespace App\Http\Controllers\sales;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Helpers\NumeroALetras;
use App\Services\NubeFactService;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;

class SalesController extends Controller
{
    #[Middleware('auth')]
    public function index()
    {
        //dd(Date('Y-m-d'));
        //$voucher = Voucher::with(['businessLocation.business', 'customer', 'items.variant.product', 'items.variant.size'])->find(10);
        //$service = app(NubeFactService::class);
        //$resultado = $service->enviar($voucher);
        //dd($resultado);
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
