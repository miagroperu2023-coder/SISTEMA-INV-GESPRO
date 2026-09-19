<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'voucher_id',
        'metodo_pago',
        'monto',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
