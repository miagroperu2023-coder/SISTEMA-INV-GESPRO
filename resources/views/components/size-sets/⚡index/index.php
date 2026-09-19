<?php

use Livewire\Component;
use App\Models\BusinessLocation;
use App\Models\SizeSet;

new class extends Component
{
    public $mostrarForm = false;
    public $nuevo_tipo = 'letra';
    public $nuevo_valor;

    public function abrirForm()
    {
        $this->reset(['nuevo_tipo', 'nuevo_valor']);
        $this->mostrarForm = true;
    }

    protected function negocioIdActivo()
    {
        return BusinessLocation::find(session('sede_activa_id'))?->business_id;
    }

    public function crearTalla()
    {
        $this->validate([
            'nuevo_tipo' => 'required|in:letra,numero,calzado,unico',
            'nuevo_valor' => 'required|string|max:20',
        ]);

        $negocioId = $this->negocioIdActivo();

        // busca primero si ya existe como talla estándar (global) o ya creada por ESTE negocio
        $size = SizeSet::where('tipo', $this->nuevo_tipo)
            ->where('valor', $this->nuevo_valor)
            ->where(function ($q) use ($negocioId) {
                $q->whereNull('business_id')->orWhere('business_id', $negocioId);
            })
            ->first();

        if (!$size) {
            $size = SizeSet::create([
                'tipo' => $this->nuevo_tipo,
                'valor' => $this->nuevo_valor,
                'orden' => SizeSet::where('tipo', $this->nuevo_tipo)->max('orden') + 1,
                'business_id' => $negocioId, // queda marcada como propia de este negocio
            ]);
        }

        $sede = BusinessLocation::find(session('sede_activa_id'));

        if (!$sede->sizeSets()->where('size_set_id', $size->id)->exists()) {
            $sede->sizeSets()->attach($size->id, ['estado' => 'ACTIVO']);
        } else {
            $sede->sizeSets()->updateExistingPivot($size->id, ['estado' => 'ACTIVO']);
        }

        $this->mostrarForm = false;
        session()->flash('ok', "Talla '{$size->valor}' agregada y activada.");
    }

    public function toggle($sizeSetId)
    {
        $sede = BusinessLocation::find(session('sede_activa_id'));

        $pivotActual = $sede->sizeSets()->where('size_set_id', $sizeSetId)->first();

        if ($pivotActual) {
            $nuevoEstado = $pivotActual->pivot->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
            $sede->sizeSets()->updateExistingPivot($sizeSetId, ['estado' => $nuevoEstado]);
        } else {
            $sede->sizeSets()->attach($sizeSetId, ['estado' => 'ACTIVO']);
        }
    }

    public function render()
    {
        $sede = BusinessLocation::with('sizeSets')->find(session('sede_activa_id'));
        $negocioId = $this->negocioIdActivo();

        // solo tallas estándar (business_id null) + las creadas por ESTE negocio
        $todasLasTallas = SizeSet::where(function ($q) use ($negocioId) {
            $q->whereNull('business_id')->orWhere('business_id', $negocioId);
        })
            ->orderBy('tipo')->orderBy('orden')
            ->get()
            ->groupBy('tipo');

        $estadosPorTalla = $sede->sizeSets->mapWithKeys(fn($size) => [$size->id => $size->pivot->estado]);

        return $this->view([
            'todasLasTallas' => $todasLasTallas,
            'estadosPorTalla' => $estadosPorTalla,
        ]);
    }
};
