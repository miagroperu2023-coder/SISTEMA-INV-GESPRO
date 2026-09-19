<?php

use Livewire\Component;
use App\Models\Customer;
use App\Models\ProductVariant;
use App\Models\Voucher;

new class extends Component
{
    public $busqueda = '';
    public $resultados = [];
    public $items = [];

    public $tipo_comprobante = 'ticket';
    public $customer_id = null;
    public $buscarCliente = '';
    public $clientesEncontrados = [];

    public $pagos = [];

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

    public function updatedBuscarCliente()
    {
        if (strlen($this->buscarCliente) < 2) {
            $this->clientesEncontrados = [];
            return;
        }

        $this->clientesEncontrados = Customer::where('business_location_id', session('sede_activa_id'))
            ->where(function ($q) {
                $q->where('numero_documento', 'like', '%' . $this->buscarCliente . '%')
                    ->orWhere('nombre_razon_social', 'like', '%' . $this->buscarCliente . '%');
            })->limit(5)->get();
    }

    public function seleccionarCliente($clienteId)
    {
        $this->customer_id = $clienteId;
        $this->buscarCliente = '';
        $this->clientesEncontrados = [];
    }

    public function agregarPago()
    {
        $this->pagos[] = ['metodo_pago' => 'efectivo', 'monto' => 0];
    }

    public function quitarPago($index)
    {
        unset($this->pagos[$index]);
        $this->pagos = array_values($this->pagos);
    }

    public function getTotalProperty()
    {
        return collect($this->items)->sum(fn($item) => $item['precio_venta'] * $item['cantidad']);
    }

    public function getTotalPagadoProperty()
    {
        return collect($this->pagos)->sum('monto');
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

        if (empty($this->items)) {
            session()->flash('error', 'Agrega al menos un producto.');
            return;
        }

        if ($this->tipo_comprobante !== 'ticket' && !$this->customer_id) {
            session()->flash('error', 'Selecciona un cliente para emitir boleta/factura.');
            return;
        }

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

        $voucher = Voucher::create([
            'cashier_shift_id' => session('cashier_shift_id'),
            'customer_id' => $this->customer_id,
            'tipo_comprobante' => $this->tipo_comprobante,
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

        $this->reset(['items', 'pagos', 'customer_id', 'tipo_comprobante']);
        session()->flash('ok', 'Venta registrada correctamente.');
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
