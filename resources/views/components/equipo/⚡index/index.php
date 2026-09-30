<?php

use Livewire\Component;
use App\Models\Business;
use App\Models\BusinessLocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

new class extends Component
{
    public $mostrarForm = false;
    public $name;
    public $email;
    public $password;
    public $business_location_id;

    public $editandoId = null;
    public $nuevaPassword = '';

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

        if (!$business->locations()->where('id', $this->business_location_id)->exists()) {
            session()->flash('error', 'Esa sede no pertenece a tu negocio.');
            return;
        }

        $vendedor = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $vendedor->belongsToMany(BusinessLocation::class, 'business_location_user')
            ->attach($this->business_location_id, ['estado' => 'ACTIVO']);

        $this->mostrarForm = false;
        session()->flash('ok', "Cuenta creada para {$vendedor->name}. Comparte estas credenciales: {$vendedor->email}");
    }

    public function abrirEditarPassword($userId)
    {
        $this->editandoId = $this->editandoId === $userId ? null : $userId;
        $this->nuevaPassword = '';
    }

    public function actualizarPassword($userId)
    {
        $this->validate(['nuevaPassword' => ['required', Password::min(6)]]);

        User::where('id', $userId)->update([
            'password' => Hash::make($this->nuevaPassword),
        ]);

        $this->editandoId = null;
        session()->flash('ok', 'Contraseña actualizada correctamente.');
    }

    public function toggleEstado($userId, $sedeId)
    {
        $pivot = DB::table('business_location_user')
            ->where('business_location_id', $sedeId)
            ->where('user_id', $userId)
            ->first();

        $nuevoEstado = $pivot->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';

        DB::table('business_location_user')
            ->where('business_location_id', $sedeId)
            ->where('user_id', $userId)
            ->update(['estado' => $nuevoEstado]);

        session()->flash('ok', $nuevoEstado === 'ACTIVO' ? 'Vendedor activado.' : 'Vendedor desactivado. Ya no podrá vender en esta sede.');
    }

    public function render()
    {
        $business = $this->negocioDelDueno();
        $sedes = $business?->locations ?? collect();

        $vendedores = User::whereHas('sedesAsignadas', function ($q) use ($sedes) {
            $q->whereIn('business_location_id', $sedes->pluck('id'));
        })
            ->with(['sedesAsignadas' => fn($q) => $q->whereIn('business_location_id', $sedes->pluck('id'))])
            ->get();

        return $this->view([
            'sedes' => $sedes,
            'vendedores' => $vendedores,
        ]);
    }
};
