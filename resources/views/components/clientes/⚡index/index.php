<?php

use Livewire\Component;
use App\Models\Customer;
use App\Services\ReniecService;
use App\Services\SunatService;

new class extends Component
{
    public $buscar = '';

    // form de registro
    public $mostrarForm = false;
    public $tipo_documento = 'dni';
    public $numero_documento = '';
    public $nombre_razon_social = '';
    public $direccion = '';
    public $buscandoApi = false;
    public $mensajeApi = null;

    public function abrirForm()
    {
        $this->reset(['tipo_documento', 'numero_documento', 'nombre_razon_social', 'direccion', 'mensajeApi']);
        $this->mostrarForm = true;
    }

    public function buscarEnApi(ReniecService $reniec, SunatService $sunat)
    {
        $this->mensajeApi = null;
        $numero = trim($this->numero_documento);

        if ($this->tipo_documento === 'dni') {
            if (strlen($numero) !== 8 || !ctype_digit($numero)) {
                $this->mensajeApi = 'Ingresa un DNI válido de 8 dígitos.';
                return;
            }
        } else {
            if (strlen($numero) !== 11 || !ctype_digit($numero)) {
                $this->mensajeApi = 'Ingresa un RUC válido de 11 dígitos.';
                return;
            }
        }

        // evita duplicar si ya existe
        $existente = Customer::where('business_location_id', session('sede_activa_id'))
            ->where('numero_documento', $numero)
            ->first();

        if ($existente) {
            $this->mensajeApi = "Este documento ya está registrado como: {$existente->nombre_razon_social}";
            return;
        }

        $this->buscandoApi = true;

        if ($this->tipo_documento === 'dni') {
            $datos = $reniec->consultar($numero);

            if ($datos) {
                $this->nombre_razon_social = trim("{$datos['nombre']} {$datos['apellido_paterno']} {$datos['apellido_materno']}");
                $this->direccion = $datos['direccion'];
                $this->mensajeApi = null;
            } else {
                $this->mensajeApi = 'No se encontró en RENIEC. Completa los datos manualmente.';
            }
        } else {
            $datos = $sunat->consultar($numero);

            if ($datos) {
                $this->nombre_razon_social = $datos['razon_social'];
                $this->direccion = $datos['direccion_completa'] ?? $datos['direccion'];
                $this->mensajeApi = null;
            } else {
                $this->mensajeApi = 'No se encontró en SUNAT. Completa los datos manualmente.';
            }
        }

        $this->buscandoApi = false;
    }

    public function guardar()
    {
        $this->validate([
            'tipo_documento' => 'required|in:dni,ruc',
            'numero_documento' => 'required|string|max:20',
            'nombre_razon_social' => 'required|string|max:255',
        ]);

        $yaExiste = Customer::where('business_location_id', session('sede_activa_id'))
            ->where('numero_documento', $this->numero_documento)
            ->exists();

        if ($yaExiste) {
            session()->flash('error', 'Ya existe un cliente registrado con ese documento.');
            return;
        }

        Customer::create([
            'business_location_id' => session('sede_activa_id'),
            'tipo_documento' => $this->tipo_documento,
            'numero_documento' => $this->numero_documento,
            'nombre_razon_social' => $this->nombre_razon_social,
            'direccion' => $this->direccion,
        ]);

        $this->mostrarForm = false;
        session()->flash('ok', 'Cliente registrado con éxito. Ya puedes buscarlo en Ventas.');
    }

    public function render()
    {
        return $this->view([
            'clientes' => Customer::where('business_location_id', session('sede_activa_id'))
                ->when($this->buscar, fn($q) => $q->where('numero_documento', 'like', '%' . $this->buscar . '%')
                    ->orWhere('nombre_razon_social', 'like', '%' . $this->buscar . '%'))
                ->latest()
                ->get(),
        ]);
    }
};
