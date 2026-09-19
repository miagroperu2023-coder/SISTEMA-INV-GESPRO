<?php

use Livewire\Component;
use App\Exports\VentasExport;
use App\Models\Payment;
use App\Models\Voucher;
use App\Models\VoucherItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component
{
    public $fecha_inicio;
    public $fecha_fin;

    public function mount()
    {
        $this->fecha_inicio = now()->startOfMonth()->toDateString();
        $this->fecha_fin = now()->toDateString();
    }

    protected function obtenerFilas()
    {
        return Voucher::with('items.variant.product', 'items.variant.size', 'payments')
            ->where('business_location_id', session('sede_activa_id'))
            ->whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin])
            ->get()
            ->flatMap(function ($voucher) {
                return $voucher->items->map(function ($item) use ($voucher) {
                    return (object) [
                        'fecha' => $voucher->fecha,
                        'comprobante' => strtoupper($voucher->tipo_comprobante) . ($voucher->serie ? "-{$voucher->serie}-{$voucher->numero}" : ''),
                        'producto' => $item->variant->product->nombre,
                        'talla' => $item->variant->size->valor,
                        'color' => $item->variant->color,
                        'cantidad' => $item->cantidad,
                        'precio_venta' => $item->precio_venta,
                        'subtotal' => $item->subtotal,
                    ];
                });
            });
    }

    protected function obtenerTotalesPorMetodo()
    {
        return Payment::whereHas('voucher', function ($q) {
            $q->where('business_location_id', session('sede_activa_id'))
                ->whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin]);
        })
            ->selectRaw('metodo_pago, SUM(monto) as total')
            ->groupBy('metodo_pago')
            ->pluck('total', 'metodo_pago');
    }

    protected function obtenerGanancia()
    {
        $items = VoucherItem::with('variant')
            ->whereHas('voucher', function ($q) {
                $q->where('business_location_id', session('sede_activa_id'))
                    ->whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin]);
            })
            ->get();

        $totalVentas = $items->sum('subtotal');
        $totalCosto = $items->sum(fn($item) => $item->variant->precio_compra * $item->cantidad);

        return [
            'totalVentas' => $totalVentas,
            'totalCosto' => $totalCosto,
            'ganancia' => $totalVentas - $totalCosto,
        ];
    }

    public function exportarExcel()
    {
        return Excel::download(
            new VentasExport($this->fecha_inicio, $this->fecha_fin),
            'ventas_' . $this->fecha_inicio . '_a_' . $this->fecha_fin . '.xlsx'
        );
    }

    public function exportarPdf()
    {
        $filas = $this->obtenerFilas();
        $totalesPorMetodo = $this->obtenerTotalesPorMetodo();

        $pdf = Pdf::loadView('report.ventas-pdf', [
            'filas' => $filas,
            'totalesPorMetodo' => $totalesPorMetodo,
            'totalGeneral' => $filas->sum('subtotal'),
            'fechaInicio' => $this->fecha_inicio,
            'fechaFin' => $this->fecha_fin,
        ]);

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'ventas_' . $this->fecha_inicio . '_a_' . $this->fecha_fin . '.pdf'
        );
    }

    public function render()
    {
        return $this->view([
            'filas' => $this->obtenerFilas(),
            'totalesPorMetodo' => $this->obtenerTotalesPorMetodo(),
            'resumenGanancia' => $this->obtenerGanancia(),
        ]);
    }
};
