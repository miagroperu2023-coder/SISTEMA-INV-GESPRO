<?php

use Livewire\Component;
use Illuminate\Validation\Rule;

new class extends Component
{
    public $nombre_comercial;
    public $tipo_documento;
    public $numero_documento;
    public $razon_social;
    public $regimen_tributario;

    // NubeFact
    public $nubefact_token = '';
    public $facturacion_electronica_activa = false;

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

        $this->nubefact_token = $business->nubefact_token ?? '';
        $this->facturacion_electronica_activa = (bool) $business->facturacion_electronica_activa;
    }

    public function guardar()
    {
        $business = $this->negocioDelDueno();

        $this->validate([
            'nombre_comercial' => 'required|string|max:255',
            'tipo_documento' => 'required|in:ruc,sin_ruc',
            'numero_documento' => [
                'required_if:tipo_documento,ruc',
                'nullable',
                'string',
                'max:20',
                Rule::unique('businesses', 'numero_documento')->ignore($business->id),
            ],
            'razon_social' => 'required_if:tipo_documento,ruc|nullable|string|max:255',
            'regimen_tributario' => 'required_if:tipo_documento,ruc|nullable|in:nrus,general',
        ], [
            'numero_documento.unique' => 'Este RUC ya está registrado por otro negocio en el sistema.',
        ]);

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

    public function guardarToken()
    {
        $business = $this->negocioDelDueno();

        if ($business->tipo_documento !== 'ruc') {
            session()->flash('error', 'Primero guarda el RUC del negocio antes de conectar la facturación electrónica.');
            return;
        }

        $this->validate([
            'nubefact_token' => 'required|string',
        ]);

        $business->update([
            'nubefact_token' => $this->nubefact_token,
            'facturacion_electronica_activa' => true,
        ]);

        $this->facturacion_electronica_activa = true;
        session()->flash('ok', 'Conexión con NubeFact activada correctamente.');
    }

    public function desactivarNubefact()
    {
        $this->negocioDelDueno()->update(['facturacion_electronica_activa' => false]);
        $this->facturacion_electronica_activa = false;
        session()->flash('ok', 'Facturación electrónica desactivada. Las ventas se seguirán registrando, pero no se enviarán a SUNAT.');
    }

    public function render()
    {
        return $this->view();
    }
};
