<?php

use Livewire\Component;
use App\Models\Business;
use App\Models\BusinessLocation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

new class extends Component
{
    public $mostrarForm = false;
    public $name;
    public $email;
    public $password;
    public $business_location_id;

    protected function negocioDelDueno()
    {
        return auth()->user()->businesses()->wherePivot('rol', 'dueño')->first();
    }

    public function abrirForm()
    {
        $this->reset(['name', 'email', 'password', 'business_location_id']);
        $this->mostrarForm = true;
    }

    public function crearVendedor()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', Password::min(6)],
            'business_location_id' => 'required|exists:business_locations,id',
        ]);

        $business = $this->negocioDelDueno();

        // valida que la sede elegida realmente pertenezca al negocio de este dueño
        if (!$business->locations()->where('id', $this->business_location_id)->exists()) {
            session()->flash('error', 'Esa sede no pertenece a tu negocio.');
            return;
        }

        $vendedor = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        // lo asigna SOLO a esa sede (no entra a business_user, así no ve el resto)
        $vendedor->belongsToMany(BusinessLocation::class, 'business_location_user')
            ->attach($this->business_location_id);

        $this->mostrarForm = false;
        session()->flash('ok', "Cuenta creada para {$vendedor->name}. Comparte estas credenciales: {$vendedor->email} / (la contraseña que ingresaste)");
    }

    public function render()
    {
        $business = $this->negocioDelDueno();

        $sedes = $business?->locations ?? collect();

        $vendedores = User::whereHas('sedesAsignadas', function ($q) use ($sedes) {
            $q->whereIn('business_location_id', $sedes->pluck('id'));
        })->with('sedesAsignadas')->get();

        return $this->view([
            'sedes' => $sedes,
            'vendedores' => $vendedores,
        ]);
    }
};
