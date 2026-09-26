<?php

namespace App\Console\Commands;

use App\Models\Voucher;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use App\Services\NubeFactService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('app:enviar-comprobantes-pendientes')]
#[Description('Envía a NubeFact las boletas/facturas pendientes de emisión')]
class EnviarComprobantesPendientes extends Command
{
    public function handle(NubeFactService $service)
    {
        Log::info('sunat:enviar-pendientes → inicio de ejecución');

        $pendientes = Voucher::with([
            'businessLocation.business',
            'customer',
            'items.variant.product',
            'items.variant.size',
        ])
            ->whereIn('tipo_comprobante', ['boleta', 'factura'])
            ->where('estado', 'pendiente')
            ->limit(50)
            ->get();

        if ($pendientes->isEmpty()) {
            Log::info('sunat:enviar-pendientes → no hay pendientes');
            $this->info('No hay comprobantes pendientes.');
            return;
        }

        Log::info('sunat:enviar-pendientes → procesando ' . $pendientes->count() . ' comprobante(s)');

        foreach ($pendientes as $voucher) {
            try {
                $business = $voucher->businessLocation?->business;

                if (!$business || !$business->puedeEmitirElectronico()) {
                    continue;
                }

                $resultado = $service->enviar($voucher);
                $data = $resultado['respuesta'];

                if (isset($data['errors'])) {
                    $nuevoEstado = 'rechazado';
                } elseif ($data['aceptada_por_sunat'] ?? false) {
                    $nuevoEstado = 'aceptado';
                } else {
                    $nuevoEstado = 'pendiente';
                }

                $voucher->update([
                    'estado' => $nuevoEstado,
                    'respuesta_proveedor' => $data,
                    'pdf_url' => $data['enlace_del_pdf'] ?? null,
                    'xml_url' => $data['enlace_del_xml'] ?? null,
                    'cdr_url' => $data['enlace_del_cdr'] ?? null,
                ]);

                Log::info("sunat:enviar-pendientes → voucher {$voucher->id} = {$nuevoEstado}");
                $this->info("Voucher {$voucher->id} → " . strtoupper($nuevoEstado));
            } catch (\Throwable $e) {
                Log::error('sunat:enviar-pendientes → error en voucher', [
                    'voucher_id' => $voucher->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Voucher {$voucher->id} falló: {$e->getMessage()}");
            }
        }

        Log::info('sunat:enviar-pendientes → fin de ejecución');
    }
}
