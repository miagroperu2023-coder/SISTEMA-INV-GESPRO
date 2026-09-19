<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['business_location_id', 'nombre', 'estado'];

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

    public function scopeActivas($query)
    {
        return $query->where('estado', 'ACTIVO');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'categoria_id');
    }
}
