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
        return $this->belongsToMany(Business::class, 'business_users')->withPivot('rol')->withTimestamps();
    }

    public function sedesAsignadas()
    {
        return $this->belongsToMany(BusinessLocation::class, 'business_location_user')->withTimestamps();
    }

    public function sedesAccesibles()
    {
        // sedes donde es dueño o admin del negocio completo → ve TODAS las sedes de ese negocio
        $sedesComoDueno = $this->businesses()
            ->wherePivotIn('rol', ['dueño', 'admin'])
            ->with('locations')
            ->get()
            ->pluck('locations')
            ->flatten();

        // sedes donde es vendedor asignado directamente → ve SOLO esa sede
        $sedesComoVendedor = $this->belongsToMany(BusinessLocation::class, 'business_location_user')->get();

        return $sedesComoDueno->merge($sedesComoVendedor)->unique('id');
    }

    //para saber el rol de la sede activa
    public function rolEnSedeActiva(): ?string
    {
        $sedeId = session('sede_activa_id');

        // si es dueño/admin del negocio dueño de esa sede
        $business = $this->businesses()
            ->whereHas('locations', fn($q) => $q->where('id', $sedeId))
            ->first();

        if ($business) {
            return $business->pivot->rol; // 'dueño' o 'admin'
        }

        // si es vendedor asignado directo a esa sede
        if ($this->sedesAsignadas()->where('business_location_id', $sedeId)->exists()) {
            return 'vendedor';
        }

        return null;
    }

    public function esDuenoOAdmin(): bool
    {
        return in_array($this->rolEnSedeActiva(), ['dueño', 'admin']);
    }
}
