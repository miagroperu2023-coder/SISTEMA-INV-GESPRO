<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    protected $fillable = [
        'nombre_comercial',
        'tipo_documento',
        'numero_documento',
        'razon_social',
        'regimen_tributario',
    ];

    public function locations(): HasMany
    {
        return $this->hasMany(BusinessLocation::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_users')->withPivot('rol')->withTimestamps();
    }

    public function comprobantesPermitidos(): array
    {
        return match ($this->regimen_tributario) {
            'sin_ruc' => ['ticket'],
            'nrus' => ['ticket', 'boleta'],
            'general' => ['ticket', 'boleta', 'factura'],
            default => ['ticket'],
        };
    }

    public function discriminaIgv(): bool
    {
        return $this->regimen_tributario === 'general';
    }

    public static function crearConSedeInicial(array $datosNegocio, string $nombreSede): self
    {
        $business = self::create($datosNegocio);
        $business->agregarSede($nombreSede);
        return $business;
    }

    public function agregarSede(string $nombre, ?string $direccion = null, ?string $telefono = null): BusinessLocation
    {
        $sede = $this->locations()->create([
            'nombre' => $nombre,
            'direccion' => $direccion,
            'telefono' => $telefono,
        ]);

        $sede->cashiers()->create(['nombre' => 'Caja 1']);

        VoucherSeries::create(['business_location_id' => $sede->id, 'tipo_comprobante' => 'boleta', 'serie' => 'B001']);
        VoucherSeries::create(['business_location_id' => $sede->id, 'tipo_comprobante' => 'factura', 'serie' => 'F001']);

        // YA NO se auto-activa ninguna talla — el dueño las activa manualmente en /tallas
        return $sede;
    }
}
