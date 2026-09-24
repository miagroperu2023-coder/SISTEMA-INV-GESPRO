<?php

namespace App\Exports;

use App\Models\Voucher;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VentasExport implements FromCollection, WithHeadings, WithMapping
{
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($fechaInicio, $fechaFin)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function collection(): Collection
    {
        return Voucher::with('items.variant.product', 'items.variant.size', 'payments')
            ->where('business_location_id', session('sede_activa_id'))
            ->whereBetween('fecha', [$this->fechaInicio, $this->fechaFin])
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
                        'valor_venta' => $item->valor_venta,
                        'igv' => $item->igv,
                        'subtotal' => $item->subtotal,
                        'metodos_pago' => $voucher->payments->pluck('metodo_pago')->join(', '),
                    ];
                });
            });
    }

    public function headings(): array
    {
        return ['Fecha', 'Comprobante', 'Producto', 'Talla', 'Color', 'Cantidad', 'Precio venta', 'Valor venta', 'IGV', 'Subtotal', 'Métodos de pago'];
    }

    public function map($row): array
    {
        return [
            $row->fecha->format('d/m/Y'),
            $row->comprobante,
            $row->producto,
            $row->talla,
            $row->color,
            $row->cantidad,
            number_format($row->precio_venta, 2),
            number_format($row->valor_venta, 2),
            number_format($row->igv, 2),
            number_format($row->subtotal, 2),
            $row->metodos_pago,
        ];
    }
}
