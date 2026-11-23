<?php

use Livewire\Component;
use App\Models\Category;
use App\Models\Investment;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $mostrarForm = false;

    public $fecha;
    public $concepto = '';
    public $proveedor = '';
    public $total = null;
    public $notas = '';
    public $pagos = [
        ['metodo_pago' => 'yape', 'monto' => null],
    ];

    // filtros del listado
    public $fecha_inicio;
    public $fecha_fin;

    public function mount()
    {
        $this->fecha = now()->toDateString();
        $this->fecha_inicio = now()->startOfMonth()->toDateString();
        $this->fecha_fin = now()->toDateString();
    }

    public function abrirForm()
    {
        $this->resetErrorBag();
        $this->reset(['concepto', 'proveedor', 'total', 'notas']);
        $this->fecha = now()->toDateString();
        $this->pagos = [['metodo_pago' => 'yape', 'monto' => null]];
        $this->mostrarForm = true;
    }

    public function agregarPago()
    {
        $this->pagos[] = ['metodo_pago' => 'efectivo', 'monto' => null];
    }

    public function quitarPago($index)
    {
        unset($this->pagos[$index]);
        $this->pagos = array_values($this->pagos);

        if (empty($this->pagos)) {
            $this->pagos = [['metodo_pago' => 'yape', 'monto' => null]];
        }
    }

    // llena el monto que falta para llegar al total
    public function completarMonto($index)
    {
        $sumaOtros = collect($this->pagos)
            ->reject(fn($p, $i) => $i === $index)
            ->sum(fn($p) => (float) ($p['monto'] ?? 0));

        $this->pagos[$index]['monto'] = max(0, round((float) $this->total - $sumaOtros, 2));
    }

    public function getTotalPagadoProperty()
    {
        return collect($this->pagos)->sum(fn($p) => (float) ($p['monto'] ?? 0));
    }

    public function guardar()
    {
        $this->validate([
            'fecha' => 'required|date',
            'concepto' => 'required|string|max:255',
            'proveedor' => 'nullable|string|max:255',
            'total' => 'required|numeric|min:0.01',
            'notas' => 'nullable|string|max:500',
            'pagos' => 'required|array|min:1',
            'pagos.*.metodo_pago' => 'required|in:yape,plin,efectivo,tarjeta',
            'pagos.*.monto' => 'required|numeric|min:0.01',
        ], [
            'concepto.required' => 'Indica qué compraste (ej. Jeans, Blusas, Polos).',
            'total.required' => 'Ingresa el monto total de la inversión.',
            'total.min' => 'El monto total debe ser mayor a 0.',
            'pagos.*.monto.required' => 'Ingresa el monto de cada pago (o quita la fila que no uses).',
            'pagos.*.monto.min' => 'Cada pago debe ser mayor a 0.',
        ]);

        if (round($this->totalPagado, 2) !== round((float) $this->total, 2)) {
            session()->flash('error', 'La suma de los pagos no coincide con el total de la inversión.');
            return;
        }

        DB::transaction(function () {
            $inversion = Investment::create([
                'user_id' => auth()->id(),
                'fecha' => $this->fecha,
                'concepto' => $this->concepto,
                'proveedor' => $this->proveedor ?: null,
                'total' => $this->total,
                'notas' => $this->notas ?: null,
            ]);

            foreach ($this->pagos as $pago) {
                $inversion->payments()->create([
                    'metodo_pago' => $pago['metodo_pago'],
                    'monto' => $pago['monto'],
                ]);
            }
        });

        $this->mostrarForm = false;
        session()->flash('ok', 'Inversión registrada con éxito.');
    }

    // anula o restaura (no se borra nada, queda el historial)
    public function cambiarEstado($inversionId)
    {
        $inversion = Investment::find($inversionId);

        if ($inversion) {
            $inversion->update([
                'estado' => $inversion->estado === 'ACTIVO' ? 'ANULADO' : 'ACTIVO',
            ]);
        }
    }

    public function render()
    {
        $inversiones = Investment::with('payments', 'user')
            ->whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        $activas = $inversiones->where('estado', 'ACTIVO');

        $porMetodo = $activas
            ->flatMap(fn($inv) => $inv->payments)
            ->groupBy('metodo_pago')
            ->map(fn($grupo) => $grupo->sum('monto'));

        return $this->view([
            'inversiones' => $inversiones,
            'totalInvertido' => $activas->sum('total'),
            'porMetodo' => $porMetodo,
            'conceptos' => Category::where('estado', 'ACTIVO')->pluck('nombre'),
        ]);
    }
};
