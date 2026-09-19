<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashierShift extends Model
{
    protected $fillable = [
        'cashier_id',
        'user_id',
        'monto_apertura',
        'monto_cierre_esperado',
        'monto_cierre_real',
        'diferencia',
        'abierta_at',
        'cerrada_at',
    ];

    protected $casts = [
        'abierta_at' => 'datetime',
        'cerrada_at' => 'datetime',
    ];

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(Cashier::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
