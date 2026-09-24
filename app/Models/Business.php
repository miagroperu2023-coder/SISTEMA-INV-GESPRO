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
        'estado_suscripcion',
        'suscripcion_vence_el',
        'nubefact_token',
        'nubefact_ruta',
        'facturacion_electronica_activa',
    ];

    protected $casts = [
        'suscripcion_vence_el' => 'date',
        'nubefact_token' => 'encrypted',
    ];

    public function locations(): HasMany
    {
        return $this->hasMany(BusinessLocation::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_users')
            ->using(BusinessUser::class)
            ->withPivot('rol')
            ->withTimestamps();
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

    public function puedeEmitirElectronico(): bool
    {
        return $this->tipo_documento === 'ruc'
            && $this->facturacion_electronica_activa
            && !empty($this->nubefact_token)
            && !empty($this->nubefact_ruta);
    }

    public function agregarSede(string $nombre, ?string $direccion = null, ?string $telefono = null): BusinessLocation
    {
        $sede = $this->locations()->create([
            'nombre' => $nombre,
            'direccion' => $direccion,
            'telefono' => $telefono,
        ]);

        $sede->cashiers()->create(['nombre' => 'Caja 1']);

        if ($this->tipo_documento === 'ruc') {
            VoucherSeries::create(['business_location_id' => $sede->id, 'tipo_comprobante' => 'boleta', 'serie' => 'B001']);
            VoucherSeries::create(['business_location_id' => $sede->id, 'tipo_comprobante' => 'factura', 'serie' => 'F001']);
        }

        return $sede;
    }

    public static function crearConSedeInicial(array $datosNegocio, string $nombreSede): self
    {
        $business = self::create($datosNegocio);
        $business->agregarSede($nombreSede);
        return $business;
    }
}
