<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Voucher;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NubeFactService
{
    public function enviar(Voucher $voucher): array
    {
        $business = $voucher->businessLocation->business;

        if (!$business->puedeEmitirElectronico()) {
            throw new \RuntimeException('Este negocio no tiene la facturación electrónica activa o configurada.');
        }

        $payload = $this->armarPayload($voucher, $business);

        $respuesta = Http::withHeaders([
            'Authorization' => 'Token token="' . $business->nubefact_token . '"',
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($business->nubefact_ruta, $payload);

        $data = $respuesta->json();

        // NubeFact devuelve HTTP 200 incluso ante errores de validación,
        // así que hay que revisar el contenido, no solo el status HTTP.
        if ($respuesta->failed() || isset($data['errors'])) {
            Log::warning('NubeFact rechazó el comprobante', [
                'voucher_id' => $voucher->id,
                'respuesta' => $data,
            ]);

            return [
                'aceptado' => false,
                'respuesta' => $data,
            ];
        }

        return [
            'aceptado' => (bool) ($data['aceptada_por_sunat'] ?? false),
            'respuesta' => $data,
        ];
    }

    protected function armarPayload(Voucher $voucher, Business $business): array
    {
        $discriminaIgv = $business->discriminaIgv();

        $items = $voucher->items->map(function ($item) use ($discriminaIgv) {
            return [
                'unidad_de_medida' => 'NIU',
                'codigo' => (string) $item->product_variant_id,
                'descripcion' => $item->variant->product->nombre . ' - ' . $item->variant->color . ' - Talla ' . $item->variant->size->valor,
                'cantidad' => (float) $item->cantidad,
                'valor_unitario' => round($item->valor_venta / $item->cantidad, 2),
                'precio_unitario' => (float) $item->precio_venta,
                'subtotal' => (float) $item->valor_venta,
                // 1 = Gravado - Operación Onerosa | 8 = Exonerado - Operación Onerosa
                'tipo_de_igv' => $discriminaIgv ? config('nubefact.tipo_igv_gravado', 1) : config('nubefact.tipo_igv_nrus', 8),
                'igv' => (float) $item->igv,
                'total' => (float) $item->subtotal,
            ];
        })->values()->toArray();

        return [
            'operacion' => 'generar_comprobante',
            'tipo_de_comprobante' => $voucher->tipo_comprobante === 'factura' ? 1 : 2,
            'serie' => $voucher->serie,
            'numero' => $voucher->numero,
            'sunat_transaction' => 1, // venta interna
            'cliente_tipo_de_documento' => $voucher->customer->tipo_documento === 'ruc' ? 6 : 1,
            'cliente_numero_de_documento' => $voucher->customer->numero_documento,
            'cliente_denominacion' => $voucher->customer->nombre_razon_social,
            'cliente_direccion' => $voucher->customer->direccion ?? '-',
            'fecha_de_emision' => $voucher->fecha->format('d-m-Y'),
            'moneda' => 1, // soles
            'porcentaje_de_igv' => $discriminaIgv ? 18.0 : 0.0,
            'total_gravada' => $discriminaIgv ? (float) $voucher->subtotal : 0.0,
            'total_exonerada' => $discriminaIgv ? 0.0 : (float) $voucher->subtotal,
            'total_igv' => (float) $voucher->igv_total,
            'total' => (float) $voucher->total,
            'items' => $items,
        ];
    }
}
