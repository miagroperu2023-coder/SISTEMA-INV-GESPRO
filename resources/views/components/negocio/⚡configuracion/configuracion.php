<?php

use Livewire\Component;

new class extends Component
{
    public $nombre_comercial;
    public $tipo_documento;
    public $numero_documento;
    public $razon_social;
    public $regimen_tributario;

    protected function negocioDelDueno()
    {
        return auth()->user()->businesses()->wherePivot('rol', 'dueño')->first();
    }

    public function mount()
    {
        $business = $this->negocioDelDueno();

        $this->nombre_comercial = $business->nombre_comercial;
        $this->tipo_documento = $business->tipo_documento;
        $this->numero_documento = $business->numero_documento;
        $this->razon_social = $business->razon_social;
        $this->regimen_tributario = $business->regimen_tributario;
    }

    public function guardar()
    {
        $this->validate([
            'nombre_comercial' => 'required|string|max:255',
            'tipo_documento' => 'required|in:ruc,sin_ruc',
            'numero_documento' => 'required_if:tipo_documento,ruc|nullable|string|max:20',
            'razon_social' => 'required_if:tipo_documento,ruc|nullable|string|max:255',
            'regimen_tributario' => 'required_if:tipo_documento,ruc|nullable|in:nrus,general',
        ]);

        $business = $this->negocioDelDueno();

        $business->update([
            'nombre_comercial' => $this->nombre_comercial,
            'tipo_documento' => $this->tipo_documento,
            'numero_documento' => $this->numero_documento,
            'razon_social' => $this->razon_social,
            // si es sin_ruc, se fuerza a sin_ruc sin importar lo que haya en el select
            'regimen_tributario' => $this->tipo_documento === 'ruc' ? $this->regimen_tributario : 'sin_ruc',
        ]);

        session()->flash('ok', 'Datos del negocio actualizados con éxito.');
    }

    public function render()
    {
        return $this->view();
    }
};
