<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_users')
            ->using(BusinessUser::class)
            ->withPivot('rol')
            ->withTimestamps();
    }

    public function sedesAsignadas()
    {
        return $this->belongsToMany(BusinessLocation::class, 'business_location_user')
            ->withPivot('estado')
            ->withTimestamps();
    }

    public function sedesAccesibles()
    {
        $sedesComoDueno = $this->businesses()
            ->wherePivotIn('rol', ['dueño', 'admin'])
            ->wherePivot('estado', 'ACTIVO') // el negocio-dueño debe estar activo
            ->with('locations')
            ->get()
            ->pluck('locations')
            ->flatten();

        $sedesComoVendedor = $this->belongsToMany(BusinessLocation::class, 'business_location_user')
            ->wherePivot('estado', 'ACTIVO') // solo sedes donde sigue activo como vendedor
            ->get();

        return $sedesComoDueno->merge($sedesComoVendedor)->unique('id');
    }

    public function negocioActivo(): ?\App\Models\Business
    {
        $sede = \App\Models\BusinessLocation::find(session('sede_activa_id'));
        return $sede?->business;
    }

    //para saber el rol de la sede activa
    public function rolEnSedeActiva(): ?string
    {
        $sedeId = session('sede_activa_id');

        $business = $this->businesses()
            ->wherePivot('estado', 'ACTIVO')
            ->whereHas('locations', fn($q) => $q->where('id', $sedeId))
            ->first();

        if ($business) {
            return $business->pivot->rol;
        }

        if ($this->sedesAsignadas()->wherePivot('estado', 'ACTIVO')->where('business_location_id', $sedeId)->exists()) {
            return 'vendedor';
        }

        return null;
    }

    public function esDuenoOAdmin(): bool
    {
        return in_array($this->rolEnSedeActiva(), ['dueño', 'admin']);
    }
}
