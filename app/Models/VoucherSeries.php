<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VoucherSeries extends Model
{
    protected $fillable = [
        'business_location_id',
        'tipo_comprobante',
        'serie',
        'correlativo_actual',
    ];

    public function siguienteNumero(): int
    {
        $this->increment('correlativo_actual');
        return $this->correlativo_actual;
    }

    // Reserva atómicamente el siguiente serie-número para una sede y tipo de comprobante.
    // Devuelve ['serie' => 'B001', 'numero' => 121]
    public static function reservarSiguiente(int $businessLocationId, string $tipoComprobante): array
    {
        return DB::transaction(function () use ($businessLocationId, $tipoComprobante) {
            $serie = self::where('business_location_id', $businessLocationId)
                ->where('tipo_comprobante', $tipoComprobante)
                ->where('estado', 'ACTIVO')
                ->lockForUpdate()
                ->first();

            if (!$serie) {
                throw new \RuntimeException("No hay una serie configurada para {$tipoComprobante} en esta sede. Ve a Negocio → Series de comprobantes.");
            }

            $numero = $serie->siguienteNumero();

            return ['serie' => $serie->serie, 'numero' => $numero];
        });
    }
}
