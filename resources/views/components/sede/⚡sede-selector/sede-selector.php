<?php

use Livewire\Component;

new class extends Component
{
    public $sedeActivaId = '';

    public function mount()
    {
        $this->sedeActivaId = session('sede_activa_id');

        if (!$this->sedeActivaId) {
            $primera = auth()->user()->sedesAccesibles()->first();
            if ($primera) {
                session(['sede_activa_id' => $primera->id]);
                $this->sedeActivaId = $primera->id;
            }
        }
    }

    public function cambiarSede($sedeId)
    {
        session(['sede_activa_id' => $sedeId]);
        return redirect(request()->header('Referer') ?? '/');
    }

    public function render()
    {
        return $this->view([
            'sedes' => auth()->user()->sedesAccesibles(),
        ]);
    }
};
