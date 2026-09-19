<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'business_location_id',
        'tipo_documento',
        'numero_documento',
        'nombre_razon_social',
        'direccion',
        'estado',
    ];
}
