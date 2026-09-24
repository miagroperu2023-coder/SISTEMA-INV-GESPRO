<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $fillable = [
        'business_location_id',
        'cashier_shift_id',
        'customer_id',
        'tipo_comprobante',
        'serie',
        'numero',
        'estado',
        'respuesta_proveedor',
        'pdf_url',
        'xml_url',
        'cdr_url',
        'subtotal',
        'igv_total',
        'total',
        'fecha',
    ];

    protected $casts = [
        'respuesta_proveedor' => 'array',
        'fecha' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($voucher) {
            if (empty($voucher->business_location_id) && session()->has('sede_activa_id')) {
                $voucher->business_location_id = session('sede_activa_id');
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(VoucherItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashierShift(): BelongsTo
    {
        return $this->belongsTo(CashierShift::class);
    }

    public function businessLocation(): BelongsTo
    {
        return $this->belongsTo(BusinessLocation::class);
    }
}
