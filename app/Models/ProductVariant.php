<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'size_id',
        'color',
        'sku',
        'precio_compra',
        'precio_venta',
        'stock',
        'estado',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(SizeSet::class, 'size_id');
    }
}
