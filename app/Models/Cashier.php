<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cashier extends Model
{
    protected $fillable = [
        'business_location_id',
        'nombre',
        'estado',
    ];

    public function shifts(): HasMany
    {
        return $this->hasMany(CashierShift::class);
    }
}
