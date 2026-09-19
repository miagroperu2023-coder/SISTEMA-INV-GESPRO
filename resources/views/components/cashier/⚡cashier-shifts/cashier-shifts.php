<?php

use Livewire\Component;
use App\Models\Cashier;
use App\Models\CashierShift;
use App\Models\Payment;

new class extends Component
{
    public $cashier_id;
    public $monto_apertura;
    public $monto_cierre_real;
    public $turnoAbierto = null;

    public function mount()
    {
        $this->turnoAbierto = CashierShift::whereNull('cerrada_at')
            ->where('user_id', auth()->id())
            ->first();

        if ($this->turnoAbierto) {
            session(['cashier_shift_id' => $this->turnoAbierto->id]);
        }
    }

    public function abrirTurno()
    {
        $this->validate([
            'cashier_id' => 'required|exists:cashiers,id',
            'monto_apertura' => 'required|numeric|min:0',
        ]);

        // doble seguro: revalida en el servidor por si el select quedó desactualizado
        $yaAbierta = CashierShift::where('cashier_id', $this->cashier_id)
            ->whereNull('cerrada_at')
            ->exists();

        if ($yaAbierta) {
            session()->flash('error', 'Esa caja ya fue tomada por otra persona. Elige otra.');
            $this->cashier_id = null;
            return;
        }

        $this->turnoAbierto = CashierShift::create([
            'cashier_id' => $this->cashier_id,
            'user_id' => auth()->id(),
            'monto_apertura' => $this->monto_apertura,
            'abierta_at' => now(),
        ]);

        session(['cashier_shift_id' => $this->turnoAbierto->id]);
    }

    public function cerrarTurno()
    {
        $this->validate(['monto_cierre_real' => 'required|numeric|min:0']);

        $ventasEfectivo = Payment::whereHas('voucher', fn($q) => $q->where('cashier_shift_id', $this->turnoAbierto->id))
            ->where('metodo_pago', 'efectivo')->sum('monto');

        $ingresos = $this->turnoAbierto->cashMovements()->where('tipo', 'ingreso')->sum('monto');
        $salidas = $this->turnoAbierto->cashMovements()->where('tipo', 'salida')->sum('monto');
        $esperado = $this->turnoAbierto->monto_apertura + $ventasEfectivo + $ingresos - $salidas;

        $this->turnoAbierto->update([
            'monto_cierre_esperado' => $esperado,
            'monto_cierre_real' => $this->monto_cierre_real,
            'diferencia' => $this->monto_cierre_real - $esperado,
            'cerrada_at' => now(),
        ]);

        session()->forget('cashier_shift_id');
        $this->turnoAbierto = null;
        $this->reset(['cashier_id', 'monto_apertura', 'monto_cierre_real']);
    }

    public function render()
    {
        $todasLasCajas = Cashier::where('business_location_id', session('sede_activa_id'))
            ->with(['shifts' => fn($q) => $q->whereNull('cerrada_at')->with('cashier')])
            ->get();

        // solo las que NO tienen turno abierto por nadie
        $cajasDisponibles = $todasLasCajas->filter(fn($caja) => $caja->shifts->isEmpty());

        // las ocupadas, con quién las tiene abiertas (para mostrar el aviso)
        $cajasOcupadas = $todasLasCajas->filter(fn($caja) => $caja->shifts->isNotEmpty())
            ->map(function ($caja) {
                $turno = $caja->shifts->first();
                return [
                    'nombre' => $caja->nombre,
                    'usuario' => $turno->user->name ?? 'otro usuario',
                    'desde' => $turno->abierta_at,
                ];
            });

        return $this->view([
            'cajas' => $cajasDisponibles,
            'cajasOcupadas' => $cajasOcupadas,
        ]);
    }
};
