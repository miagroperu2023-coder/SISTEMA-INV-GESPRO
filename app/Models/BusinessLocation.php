<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessLocation extends Model
{
    use HasFactory;

    protected $fillable = ['business_id', 'nombre', 'direccion', 'telefono'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function cashiers()
    {
        return $this->hasMany(Cashier::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function sizeSets()
    {
        return $this->belongsToMany(SizeSet::class, 'business_location_size_set')
            ->withPivot('estado')
            ->withTimestamps();
    }

    // helper: solo las tallas activas de esta sede, agrupadas por tipo
    public function tallasActivas()
    {
        return $this->sizeSets()
            ->wherePivot('estado', 'ACTIVO')
            ->orderBy('tipo')
            ->orderBy('orden')
            ->get()
            ->groupBy('tipo');
    }

    public function vendedores()
    {
        return $this->belongsToMany(User::class, 'business_location_user')->withTimestamps();
    }
}
