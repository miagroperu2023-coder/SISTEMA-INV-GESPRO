<?php

use Livewire\Component;
use App\Models\Business;

new class extends Component
{
    public function toggleSuscripcion($businessId)
    {
        $business = Business::find($businessId);
        $business->update([
            'estado_suscripcion' => $business->estado_suscripcion === 'ACTIVO' ? 'SUSPENDIDO' : 'ACTIVO',
        ]);
    }

    public function extenderSuscripcion($businessId, $dias = 30)
    {
        $business = Business::find($businessId);
        $desde = $business->suscripcion_vence_el && $business->suscripcion_vence_el->isFuture()
            ? $business->suscripcion_vence_el
            : now();

        $business->update([
            'estado_suscripcion' => 'ACTIVO',
            'suscripcion_vence_el' => $desde->addDays($dias),
        ]);
    }

    public function render()
    {
        return $this->view([
            'negocios' => Business::withCount('locations')->latest()->get(),
        ]);
    }
};
