<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Voucher;
use App\Services\NubeFactService;

new class extends Component
{
    use WithPagination;

    public $filtro = 'pendiente'; // pendiente | aceptado | rechazado

    public function updatedFiltro()
    {
        $this->resetPage();
    }

    public function reenviar($voucherId)
    {
        $voucher = Voucher::with([
            'businessLocation.business',
            'customer',
            'items.variant.product',
            'items.variant.size',
        ])->find($voucherId);

        if (!$voucher) {
            session()->flash('error', 'Comprobante no encontrado.');
            return;
        }

        $service = app(NubeFactService::class);

        try {
            $resultado = $service->enviar($voucher);
            $data = $resultado['respuesta'];

            if (isset($data['errors'])) {
                $nuevoEstado = 'rechazado';
            } elseif ($data['aceptada_por_sunat'] ?? false) {
                $nuevoEstado = 'aceptado';
            } else {
                $nuevoEstado = 'pendiente';
            }

            $voucher->update([
                'estado' => $nuevoEstado,
                'respuesta_proveedor' => $data,
                'pdf_url' => $data['enlace_del_pdf'] ?? null,
                'xml_url' => $data['enlace_del_xml'] ?? null,
                'cdr_url' => $data['enlace_del_cdr'] ?? null,
            ]);

            session()->flash(
                $nuevoEstado === 'rechazado' ? 'error' : 'ok',
                $nuevoEstado === 'rechazado' ? 'SUNAT/NubeFact rechazó el comprobante, revisa el detalle.' : 'Comprobante procesado correctamente.'
            );
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo enviar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $sedeId = session('sede_activa_id');

        $comprobantes = Voucher::with('customer')
            ->where('business_location_id', $sedeId)
            ->whereIn('tipo_comprobante', ['boleta', 'factura'])
            ->where('estado', $this->filtro)
            ->latest('fecha')
            ->paginate(15);

        return $this->view(compact('comprobantes'));
    }
};
