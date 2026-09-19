<?php

use Livewire\Component;
use App\Models\Cashier;

new class extends Component
{
    // form de nueva sede
    public $mostrarForm = false;
    public $nombre;
    public $direccion;
    public $telefono;

    // form de nueva caja dentro de una sede existente
    public $mostrarFormCaja = false;
    public $nombre_caja;
    public $sede_id_para_caja;

    public function abrirForm()
    {
        $this->reset(['nombre', 'direccion', 'telefono']);
        $this->mostrarForm = true;
    }

    public function guardar()
    {
        $this->validate(['nombre' => 'required|string|max:255']);

        $business = auth()->user()->businesses()->wherePivot('rol', 'dueño')->first();

        if (!$business) {
            session()->flash('error', 'No tienes permisos para crear sedes en este negocio.');
            return;
        }

        $sede = $business->agregarSede($this->nombre, $this->direccion, $this->telefono);

        $this->mostrarForm = false;
        session()->flash('ok', "Sede '{$sede->nombre}' creada con éxito.");
    }

    public function abrirFormCaja($sedeId)
    {
        $this->sede_id_para_caja = $sedeId;
        $this->nombre_caja = '';
        $this->mostrarFormCaja = true;
    }

    public function crearCaja()
    {
        $this->validate(['nombre_caja' => 'required|string|max:255']);

        Cashier::create([
            'business_location_id' => $this->sede_id_para_caja,
            'nombre' => $this->nombre_caja,
        ]);

        $this->mostrarFormCaja = false;
        session()->flash('ok', 'Caja creada con éxito.');
    }

    public function render()
    {
        $business = auth()->user()->businesses()->wherePivot('rol', 'dueño')->first();

        return $this->view([
            'sedes' => $business?->locations()->with('cashiers')->get() ?? collect(),
        ]);
    }
};
