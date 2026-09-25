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

    public $nubefact_ruta = '';
    public $nubefact_token = '';
    public $facturacion_electronica_activa = false;

    protected function negocioActivo()
    {
        $sede = \App\Models\BusinessLocation::find(session('sede_activa_id'));
        return $sede?->business;
    }

    public function mount()
    {
        $business = $this->negocioActivo();

        if (!$business) {
            session()->flash('error', 'No se encontró el negocio de la sede activa.');
            return;
        }

        $this->nombre_comercial = $business->nombre_comercial;
        $this->tipo_documento = $business->tipo_documento;
        $this->numero_documento = $business->numero_documento;
        $this->razon_social = $business->razon_social;
        $this->regimen_tributario = $business->regimen_tributario;

        $this->nubefact_ruta = $business->nubefact_ruta ?? '';
        $this->nubefact_token = $business->nubefact_token ?? '';
        $this->facturacion_electronica_activa = (bool) $business->facturacion_electronica_activa;
    }

    // se dispara automáticamente al cambiar el select (wire:model.live)
    public function updatedTipoDocumento($value)
    {
        if ($value !== 'ruc') {
            $this->numero_documento = null;
            $this->razon_social = null;
            $this->regimen_tributario = null;
        }
    }

    public function guardar()
    {
        $business = $this->negocioActivo();
        $rucAnterior = $business->numero_documento;

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

        $datos = [
            'nombre_comercial' => $this->nombre_comercial,
            'tipo_documento' => $this->tipo_documento,
        ];

        if ($this->tipo_documento === 'ruc') {
            $datos['numero_documento'] = $this->numero_documento;
            $datos['razon_social'] = $this->razon_social;
            $datos['regimen_tributario'] = $this->regimen_tributario;

            // el RUC cambió a uno DISTINTO del que tenía antes (no es el caso de sin_ruc -> ruc por primera vez)
            $rucRealmenteCambio = $rucAnterior !== null && $rucAnterior !== $this->numero_documento;

            if ($rucRealmenteCambio) {
                $datos['nubefact_token'] = null;
                $datos['nubefact_ruta'] = null;
                $datos['facturacion_electronica_activa'] = false;
                $this->nubefact_token = '';
                $this->nubefact_ruta = '';
                $this->facturacion_electronica_activa = false;

                session()->flash('ok', 'Datos actualizados. Como cambiaste el RUC, debes volver a conectar la facturación electrónica con las credenciales de NubeFact correspondientes al nuevo RUC.');
                $business->update($datos);
                return;
            }
        } else {
            $datos['numero_documento'] = null;
            $datos['razon_social'] = null;
            $datos['regimen_tributario'] = 'sin_ruc';
            $datos['facturacion_electronica_activa'] = false;
            $this->facturacion_electronica_activa = false;
        }

        $business->update($datos);

        session()->flash('ok', 'Datos del negocio actualizados con éxito.');
    }

    public function guardarToken()
    {
        $business = $this->negocioActivo();

        if ($business->tipo_documento !== 'ruc') {
            session()->flash('error', 'Primero guarda el RUC del negocio antes de conectar la facturación electrónica.');
            return;
        }

        $this->validate([
            'nubefact_ruta' => 'required|url',
            'nubefact_token' => 'required|string',
        ]);

        $business->update([
            'nubefact_ruta' => $this->nubefact_ruta,
            'nubefact_token' => $this->nubefact_token,
            'facturacion_electronica_activa' => true,
        ]);

        $this->facturacion_electronica_activa = true;
        session()->flash('ok', 'Conexión con NubeFact activada correctamente.');
    }

    public function desactivarNubefact()
    {
        $this->negocioActivo()->update(['facturacion_electronica_activa' => false]);
        $this->facturacion_electronica_activa = false;
        session()->flash('ok', 'Facturación electrónica desactivada. Las ventas se seguirán registrando, pero no se enviarán a SUNAT.');
    }

    public function render()
    {
        return $this->view();
    }
};
