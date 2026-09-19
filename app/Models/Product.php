<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'business_location_id',
        'category_id',
        'nombre',
        'descripcion',
        'imagen',
        'estado',
    ];

    protected static function booted()
    {
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
