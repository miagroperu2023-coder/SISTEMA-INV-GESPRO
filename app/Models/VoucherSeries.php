<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
