<?php

use Livewire\Component;
use App\Models\VoucherSeries;

new class extends Component
{
    public $series = [];

    public function mount()
    {
        $this->cargarSeries();
    }

    protected function cargarSeries()
    {
        $sedeId = session('sede_activa_id');

        foreach (['boleta', 'factura'] as $tipo) {
            $serie = VoucherSeries::firstOrCreate(
                ['business_location_id' => $sedeId, 'tipo_comprobante' => $tipo],
                ['serie' => $tipo === 'boleta' ? 'B001' : 'F001', 'correlativo_actual' => 0]
            );

            $this->series[$tipo] = [
                'id' => $serie->id,
                'serie' => $serie->serie,
                'correlativo_actual' => $serie->correlativo_actual,
            ];
        }
    }

    public function guardar($tipo)
    {
        $this->validate([
            "series.$tipo.serie" => 'required|string|max:10',
            "series.$tipo.correlativo_actual" => 'required|integer|min:0',
        ]);

        $sedeId = session('sede_activa_id');
        $serieNueva = strtoupper($this->series[$tipo]['serie']);

        // busca el número más alto YA EMITIDO con esta serie y tipo en esta sede
        $maximoEmitido = \App\Models\Voucher::where('business_location_id', $sedeId)
            ->where('tipo_comprobante', $tipo)
            ->where('serie', $serieNueva)
            ->max('numero');

        if ($maximoEmitido && $this->series[$tipo]['correlativo_actual'] < $maximoEmitido) {
            session()->flash('error', "No puedes poner un número menor a {$maximoEmitido}, ya existe un comprobante emitido con esa serie hasta ese número.");
            return;
        }

        VoucherSeries::where('id', $this->series[$tipo]['id'])->update([
            'serie' => $serieNueva,
            'correlativo_actual' => $this->series[$tipo]['correlativo_actual'],
        ]);

        session()->flash('ok', 'Serie actualizada correctamente.');
        $this->cargarSeries();
    }

    public function render()
    {
        return $this->view();
    }
};
