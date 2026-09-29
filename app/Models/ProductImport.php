<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImport extends Model
{
    //
    protected $fillable = ['business_location_id', 'estado', 'total_filas', 'creados'];
}
