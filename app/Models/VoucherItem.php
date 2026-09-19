<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherItem extends Model
{
    protected $fillable = [
        'voucher_id',
        'product_variant_id',
        'cantidad',
        'precio_venta',
        'valor_venta',
        'igv',
        'subtotal',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
