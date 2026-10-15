<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherChange extends Model
{
    protected $fillable = [
        'business_location_id',
        'voucher_id',
        'user_id',
        'tipo',
        'product_variant_id',
        'descripcion_anterior',
        'cantidad',
        'precio_anterior',
        'nuevo_product_variant_id',
        'descripcion_nueva',
        'precio_nuevo',
        'diferencia',
        'metodo_pago',
        'motivo',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}