<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investment extends Model
{
    protected $fillable = [
        'business_location_id',
        'user_id',
        'fecha',
        'concepto',
        'proveedor',
        'total',
        'notas',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    protected static function booted()
    {
        // cada sede ve solo sus inversiones
        static::addGlobalScope('sede', function ($query) {
            if (session()->has('sede_activa_id')) {
                $query->where('business_location_id', session('sede_activa_id'));
            }
        });

        static::creating(function ($model) {
            if (empty($model->business_location_id) && session()->has('sede_activa_id')) {
                $model->business_location_id = session('sede_activa_id');
            }
        });
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvestmentPayment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
