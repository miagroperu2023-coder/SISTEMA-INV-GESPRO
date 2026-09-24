<?php

use Livewire\Component;
use App\Models\Customer;
use App\Models\ProductVariant;
use App\Models\Voucher;
use App\Models\VoucherSeries;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $busqueda = '';
    public $resultados = [];
    public $items = [];

    public $tipo_comprobante = 'ticket';
    public $customer_id = null;

    // búsqueda de cliente por documento (DNI/RUC)
    public $numero_documento = '';
    public $clienteEncontradoNombre = null;
    public $clienteNoEncontrado = false;

    public $pagos = [
        ['metodo_pago' => 'yape', 'monto' => null],
    ];

    public function updatedBusqueda()
    {
        if (strlen($this->busqueda) < 2) {
            $this->resultados = [];
            return;
        }

        $this->resultados = ProductVariant::with('product', 'size')
            ->whereHas('product', fn($q) => $q->where('nombre', 'like', '%' . $this->busqueda . '%'))
            ->orWhere('color', 'like', '%' . $this->busqueda . '%')
            ->limit(8)->get();
    }

    public function agregarItem($variantId)
    {
        $variant = ProductVariant::with('product', 'size')->find($variantId);

        if (!$variant || $variant->stock <= 0) {
            session()->flash('error', 'No hay stock disponible de ese producto.');
            $this->busqueda = '';
            $this->resultados = [];
            return;
        }

        foreach ($this->items as $index => $item) {
            if ($item['variant_id'] == $variantId) {
                if ($item['cantidad'] + 1 > $variant->stock) {
                    session()->flash('error', "Solo hay {$variant->stock} unidad(es) en stock.");
                    return;
                }
                $this->items[$index]['cantidad']++;
                $this->busqueda = '';
                $this->resultados = [];
                return;
            }
        }

        $this->items[] = [
            'variant_id' => $variant->id,
            'nombre' => $variant->product->nombre,
            'talla' => $variant->size->valor,
            'color' => $variant->color,
            'precio_venta' => $variant->precio_venta,
            'cantidad' => 1,
        ];

        if (empty($this->pagos)) {
            $this->pagos = [['metodo_pago' => 'yape', 'monto' => null]];
        }

        $this->busqueda = '';
        $this->resultados = [];
    }

    public function quitarItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key)
    {
        if (str_ends_with($key, '.cantidad')) {
            $index = explode('.', $key)[0];
            if (!isset($this->items[$index])) return;

            $stockReal = ProductVariant::find($this->items[$index]['variant_id'])->stock;

            if ($this->items[$index]['cantidad'] > $stockReal) {
                $this->items[$index]['cantidad'] = $stockReal;
                session()->flash('error', "Solo hay {$stockReal} unidad(es) en stock.");
            }
            if ($this->items[$index]['cantidad'] < 1) {
                $this->items[$index]['cantidad'] = 1;
            }
        }
    }

    // se ejecuta al cambiar entre ticket/boleta/factura: limpia todo lo del cliente anterior
    public function updatedTipoComprobante()
    {
        $this->numero_documento = '';
        $this->customer_id = null;
        $this->clienteEncontradoNombre = null;
        $this->clienteNoEncontrado = false;
    }

    public function buscarPorDocumento()
    {
        $this->clienteEncontradoNombre = null;
        $this->clienteNoEncontrado = false;
        $this->customer_id = null;

        if (empty($this->numero_documento)) {
            session()->flash('error', 'Ingresa un número de documento para buscar.');
            return;
        }

        $cliente = Customer::where('business_location_id', session('sede_activa_id'))
            ->where('numero_documento', $this->numero_documento)
            ->first();

        if ($cliente) {
            $this->customer_id = $cliente->id;
            $this->clienteEncontradoNombre = $cliente->nombre_razon_social;
        } else {
            $this->clienteNoEncontrado = true;
        }
    }

    public function agregarPago()
    {
        $this->pagos[] = ['metodo_pago' => 'yape', 'monto' => null];
    }

    public function quitarPago($index)
    {
        unset($this->pagos[$index]);
        $this->pagos = array_values($this->pagos);
    }

    public function completarMonto($index)
    {
        $sumaOtros = collect($this->pagos)
            ->reject(fn($p, $i) => $i === $index)
            ->sum(fn($p) => (float) ($p['monto'] ?? 0));

        $faltante = $this->total - $sumaOtros;

        $this->pagos[$index]['monto'] = max(0, round($faltante, 2));
    }

    public function getTotalProperty()
    {
        return collect($this->items)->sum(fn($item) => $item['precio_venta'] * $item['cantidad']);
    }

    public function getTotalPagadoProperty()
    {
        return collect($this->pagos)->sum(fn($pago) => (float) ($pago['monto'] ?? 0));
    }

    protected function negocioActivo()
    {
        $sede = \App\Models\BusinessLocation::find(session('sede_activa_id'));

        return $sede?->business;
    }

    public function guardarVenta()
    {
        if (!session()->has('cashier_shift_id')) {
            session()->flash('error', 'Debes abrir la caja antes de registrar una venta.');
            return;
        }

        if (empty($this->items)) {
            session()->flash('error', 'Agrega al menos un producto.');
            return;
        }

        if ($this->tipo_comprobante !== 'ticket' && !$this->customer_id) {
            session()->flash('error', 'Busca y selecciona un cliente válido para emitir boleta/factura.');
            return;
        }

        $this->pagos = collect($this->pagos)->map(function ($pago) {
            $pago['monto'] = (float) ($pago['monto'] ?? 0);
            return $pago;
        })->toArray();

        if (round($this->totalPagado, 2) !== round($this->total, 2)) {
            session()->flash('error', 'La suma de los pagos no coincide con el total.');
            return;
        }

        foreach ($this->items as $item) {
            $variant = ProductVariant::find($item['variant_id']);
            if (!$variant || $variant->stock < $item['cantidad']) {
                session()->flash('error', "Ya no hay stock suficiente de {$item['nombre']}.");
                return;
            }
        }

        $business = $this->negocioActivo();

        if (!in_array($this->tipo_comprobante, $business->comprobantesPermitidos())) {
            session()->flash('error', 'Este negocio no puede emitir ese tipo de comprobante.');
            return;
        }

        $discriminaIgv = $business->discriminaIgv();

        $itemsCalculados = collect($this->items)->map(function ($item) use ($discriminaIgv) {
            $importeLinea = $item['precio_venta'] * $item['cantidad'];

            if ($discriminaIgv) {
                $valorVenta = round($importeLinea / 1.18, 2);
                $igv = round($importeLinea - $valorVenta, 2);
            } else {
                $valorVenta = $importeLinea;
                $igv = 0;
            }

            return array_merge($item, [
                'valor_venta' => $valorVenta,
                'igv' => $igv,
                'subtotal' => $importeLinea,
            ]);
        });

        try {
            $voucher = DB::transaction(function () use ($itemsCalculados) {
                $serie = null;
                $numero = null;

                if ($this->tipo_comprobante !== 'ticket') {
                    $reserva = VoucherSeries::reservarSiguiente(session('sede_activa_id'), $this->tipo_comprobante);
                    $serie = $reserva['serie'];
                    $numero = $reserva['numero'];
                }

                $voucher = Voucher::create([
                    'cashier_shift_id' => session('cashier_shift_id'),
                    'customer_id' => $this->customer_id,
                    'tipo_comprobante' => $this->tipo_comprobante,
                    'serie' => $serie,
                    'numero' => $numero,
                    'estado' => $this->tipo_comprobante === 'ticket' ? null : 'pendiente',
                    'subtotal' => $itemsCalculados->sum('valor_venta'),
                    'igv_total' => $itemsCalculados->sum('igv'),
                    'total' => $this->total,
                    'fecha' => now()->toDateString(),
                ]);

                foreach ($itemsCalculados as $item) {
                    $voucher->items()->create([
                        'product_variant_id' => $item['variant_id'],
                        'cantidad' => $item['cantidad'],
                        'precio_venta' => $item['precio_venta'],
                        'valor_venta' => $item['valor_venta'],
                        'igv' => $item['igv'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    ProductVariant::where('id', $item['variant_id'])->decrement('stock', $item['cantidad']);
                }

                foreach ($this->pagos as $pago) {
                    $voucher->payments()->create($pago);
                }

                return $voucher;
            });
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        session()->flash('ok', 'Venta registrada correctamente.');
        session()->flash('venta_id', $voucher->id);

        $this->reset(['items', 'customer_id', 'tipo_comprobante', 'numero_documento', 'clienteEncontradoNombre', 'clienteNoEncontrado']);
        $this->pagos = [['metodo_pago' => 'yape', 'monto' => null]];
    }

    public function render()
    {
        $business = $this->negocioActivo();

        return $this->view([
            'comprobantesPermitidos' => $business?->comprobantesPermitidos() ?? ['ticket'],
            'discriminaIgv' => $business?->discriminaIgv() ?? false,
        ]);
    }
};
