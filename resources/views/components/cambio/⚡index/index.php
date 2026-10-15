<?php

use Livewire\Component;
use App\Models\BusinessLocation;
use App\Models\ProductVariant;
use App\Models\VoucherChange;
use App\Models\VoucherItem;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $fecha_inicio;
    public $fecha_fin;

    // panel de anulación (se abre debajo de la fila elegida)
    public $anulandoItemId = null;
    public $cantidadAnular = 1;
    public $pagoId = null;
    public $motivo = '';

    // panel de cambio (se abre debajo de la fila elegida)
    public $cambiandoItemId = null;
    public $cantidadCambio = 1;
    public $busquedaNueva = '';
    public $resultadosNuevos = [];
    public $nuevaVariantId = null;
    public $precioNuevo = null; // precio de venta de la prenda nueva (editable; por defecto el de lista)
    public $metodoDiferencia = 'efectivo'; // cómo paga la diferencia cuando la prenda nueva es más cara

    public function mount()
    {
        abort_unless(auth()->user()->esDuenoOAdmin(), 403);

        $this->fecha_inicio = now()->subDays(7)->toDateString();
        $this->fecha_fin = now()->toDateString();
    }

    protected function itemDeLaSede($itemId)
    {
        $item = VoucherItem::with(['variant.product', 'variant.size', 'voucher.payments', 'voucher.items'])
            ->find($itemId);

        if (!$item || $item->voucher->business_location_id != session('sede_activa_id')) {
            session()->flash('error', 'No se encontró ese producto.');
            return null;
        }

        return $item;
    }

    // ───────────────────────── ANULACIÓN ─────────────────────────

    public function abrirAnular($itemId)
    {
        if ($this->anulandoItemId == $itemId) {
            $this->cancelar();
            return;
        }

        $item = $this->itemDeLaSede($itemId);
        if (!$item) return;

        if ($item->voucher->tipo_comprobante !== 'ticket') {
            session()->flash('error', 'Las boletas y facturas electrónicas no se modifican aquí: requieren una nota de crédito.');
            return;
        }

        $this->cancelar(); // cierra cualquier otro panel abierto
        $this->anulandoItemId = $item->id;
        $this->cantidadAnular = $item->cantidad;
        $this->motivo = '';
        $this->pagoId = $item->voucher->payments->count() === 1
            ? $item->voucher->payments->first()->id
            : null;
    }

    public function cancelar()
    {
        $this->reset([
            'anulandoItemId', 'cantidadAnular', 'pagoId', 'motivo',
            'cambiandoItemId', 'cantidadCambio', 'busquedaNueva',
            'resultadosNuevos', 'nuevaVariantId', 'precioNuevo', 'metodoDiferencia',
        ]);
        $this->resetErrorBag();
    }

    public function confirmarAnulacion()
    {
        $item = $this->itemDeLaSede($this->anulandoItemId);
        if (!$item) return;

        $voucher = $item->voucher;

        if ($voucher->tipo_comprobante !== 'ticket') {
            session()->flash('error', 'Las boletas y facturas electrónicas no se modifican aquí: requieren una nota de crédito.');
            return;
        }

        $this->validate([
            'cantidadAnular' => 'required|integer|min:1|max:' . $item->cantidad,
            'motivo' => 'nullable|string|max:255',
        ]);

        if ($voucher->payments->count() > 1 && !$voucher->payments->contains('id', (int) $this->pagoId)) {
            $this->addError('pagoId', 'Elige de qué pago se descuenta.');
            return;
        }

        $cantidad = (int) $this->cantidadAnular;
        $anulaLinea = $cantidad === (int) $item->cantidad;
        $anulaVenta = $anulaLinea && $voucher->items->count() === 1;
        $descripcion = $item->variant->product->nombre . ' — ' . $item->variant->color . ' — ' . $item->variant->size->valor;
        $precio = (float) $item->precio_venta;

        if ($anulaLinea) {
            $montoQuitado = (float) $item->subtotal;
        } else {
            $quedan = (int) $item->cantidad - $cantidad;
            $nuevoSubtotal = round($precio * $quedan, 2);
            $nuevoValor = round((float) $item->valor_venta / $item->cantidad * $quedan, 2);
            $montoQuitado = round((float) $item->subtotal - $nuevoSubtotal, 2);
        }

        DB::transaction(function () use ($item, $voucher, $cantidad, $anulaLinea, $anulaVenta, $descripcion, $precio, $montoQuitado, &$nuevoSubtotal, &$nuevoValor, &$quedan) {
            // la prenda vuelve al inventario
            ProductVariant::where('id', $item->product_variant_id)->increment('stock', $cantidad);

            if ($anulaLinea) {
                $item->delete();
            } else {
                $item->update([
                    'cantidad' => $quedan,
                    'valor_venta' => $nuevoValor,
                    'igv' => round($nuevoSubtotal - $nuevoValor, 2),
                    'subtotal' => $nuevoSubtotal,
                ]);
            }

            if ($anulaVenta) {
                $metodo = $voucher->payments->first()?->metodo_pago;
                $voucher->payments()->delete();
                $voucher->update([
                    'total' => 0,
                    'subtotal' => 0,
                    'igv_total' => 0,
                    'estado' => 'anulado',
                ]);
            } else {
                $metodo = $this->descontarDePagos($voucher, $montoQuitado);
                $restantes = $voucher->items()->get();
                $voucher->update([
                    'total' => $restantes->sum('subtotal'),
                    'subtotal' => $restantes->sum('valor_venta'),
                    'igv_total' => $restantes->sum('igv'),
                ]);
            }

            VoucherChange::create([
                'business_location_id' => $voucher->business_location_id,
                'voucher_id' => $voucher->id,
                'user_id' => auth()->id(),
                'tipo' => $anulaVenta ? 'anulacion_venta' : 'anulacion_producto',
                'product_variant_id' => $item->product_variant_id,
                'descripcion_anterior' => $descripcion,
                'cantidad' => $cantidad,
                'precio_anterior' => $precio,
                'diferencia' => -$montoQuitado,
                'metodo_pago' => $metodo,
                'motivo' => $this->motivo ?: null,
            ]);
        });

        $this->cancelar();
        session()->flash('ok', $anulaVenta
            ? 'Venta anulada. El producto volvió al inventario.'
            : 'Producto anulado. El stock y el total de la venta se actualizaron.');
    }

    // ───────────────────────── CAMBIO ─────────────────────────

    public function abrirCambio($itemId)
    {
        if ($this->cambiandoItemId == $itemId) {
            $this->cancelar();
            return;
        }

        $item = $this->itemDeLaSede($itemId);
        if (!$item) return;

        if ($item->voucher->tipo_comprobante !== 'ticket') {
            session()->flash('error', 'Las boletas y facturas electrónicas no se modifican aquí: requieren una nota de crédito.');
            return;
        }

        $this->cancelar(); // cierra cualquier otro panel abierto
        $this->cambiandoItemId = $item->id;
        $this->cantidadCambio = 1;
        $this->motivo = '';
        $this->pagoId = $item->voucher->payments->count() === 1
            ? $item->voucher->payments->first()->id
            : null;
    }

    public function updatedBusquedaNueva()
    {
        $texto = trim($this->busquedaNueva);

        if (strlen($texto) < 2) {
            $this->resultadosNuevos = [];
            return;
        }

        $palabras = preg_split('/\s+/', $texto);
        $sedeId = session('sede_activa_id');

        $query = ProductVariant::with('product', 'size')
            ->where('stock', '>', 0)
            ->whereHas('product', fn($q) => $q->where('business_location_id', $sedeId));

        foreach ($palabras as $palabra) {
            $query->where(function ($q) use ($palabra) {
                $q->whereHas('product', fn($qq) => $qq->where('nombre', 'like', "%{$palabra}%"))
                    ->orWhere('color', 'like', "%{$palabra}%")
                    ->orWhereHas('size', fn($qq) => $qq->where('valor', 'like', "%{$palabra}%"));
            });
        }

        $this->resultadosNuevos = $query->limit(30)->get();
    }

    public function seleccionarNueva($variantId)
    {
        $this->nuevaVariantId = (int) $variantId;
        $this->precioNuevo = ProductVariant::whereKey($variantId)->value('precio_venta');
        $this->busquedaNueva = '';
        $this->resultadosNuevos = [];
        $this->resetErrorBag('nuevaVariantId');
    }

    public function quitarNueva()
    {
        $this->nuevaVariantId = null;
        $this->precioNuevo = null;
    }

    public function confirmarCambio()
    {
        $item = $this->itemDeLaSede($this->cambiandoItemId);
        if (!$item) return;

        $voucher = $item->voucher;

        if ($voucher->tipo_comprobante !== 'ticket') {
            session()->flash('error', 'Las boletas y facturas electrónicas no se modifican aquí: requieren una nota de crédito.');
            return;
        }

        $this->validate([
            'cantidadCambio' => 'required|integer|min:1|max:' . $item->cantidad,
            'precioNuevo' => 'required|numeric|min:0.01|max:99999',
            'metodoDiferencia' => 'required|in:yape,plin,efectivo,tarjeta',
            'motivo' => 'nullable|string|max:255',
        ]);

        $nueva = $this->nuevaVariantId
            ? ProductVariant::with(['product', 'size'])->find($this->nuevaVariantId)
            : null;

        if (!$nueva || !$nueva->product || $nueva->product->business_location_id != session('sede_activa_id')) {
            $this->addError('nuevaVariantId', 'Elige la prenda nueva.');
            return;
        }

        if ((int) $nueva->id === (int) $item->product_variant_id) {
            $this->addError('nuevaVariantId', 'Elige una prenda distinta a la que se devuelve.');
            return;
        }

        $cantidad = (int) $this->cantidadCambio;
        $precioAnterior = (float) $item->precio_venta;
        $precioNuevo = round((float) $this->precioNuevo, 2); // el que escribió el vendedor (puede ser distinto al de lista)
        $diferencia = round(($precioNuevo - $precioAnterior) * $cantidad, 2); // + paga más · − se devuelve

        if ($diferencia < 0 && $voucher->payments->count() > 1 && !$voucher->payments->contains('id', (int) $this->pagoId)) {
            $this->addError('pagoId', 'Elige de qué pago se devuelve el dinero.');
            return;
        }

        $discriminaIgv = (bool) BusinessLocation::find(session('sede_activa_id'))?->business?->discriminaIgv();
        $descripcionAnterior = $item->variant->product->nombre . ' — ' . $item->variant->color . ' — ' . $item->variant->size->valor;
        $descripcionNueva = $nueva->product->nombre . ' — ' . $nueva->color . ' — ' . $nueva->size->valor;
        $precioNuevoTexto = number_format($precioNuevo, 2, '.', '');

        try {
            DB::transaction(function () use ($item, $voucher, $nueva, $cantidad, $precioAnterior, $precioNuevo, $precioNuevoTexto, $diferencia, $discriminaIgv, $descripcionAnterior, $descripcionNueva) {
                // la prenda nueva debe tener stock (se bloquea la fila para evitar ventas simultáneas)
                $nuevaBloqueada = ProductVariant::lockForUpdate()->find($nueva->id);
                if (!$nuevaBloqueada || $nuevaBloqueada->stock < $cantidad) {
                    throw new \RuntimeException('Solo hay ' . ($nuevaBloqueada->stock ?? 0) . ' unidad(es) de la prenda nueva.');
                }

                // inventario: la devuelta entra, la nueva sale
                ProductVariant::where('id', $item->product_variant_id)->increment('stock', $cantidad);
                $nuevaBloqueada->decrement('stock', $cantidad);

                // línea de la prenda devuelta
                if ($cantidad === (int) $item->cantidad) {
                    $item->delete();
                } else {
                    $quedan = (int) $item->cantidad - $cantidad;
                    $item->update(array_merge(
                        ['cantidad' => $quedan],
                        $this->calcularLinea($precioAnterior, $quedan, $discriminaIgv)
                    ));
                }

                // línea de la prenda nueva (si ya existe con el mismo precio, se suma)
                $existente = $voucher->items()
                    ->where('product_variant_id', $nueva->id)
                    ->where('precio_venta', $precioNuevoTexto)
                    ->first();

                if ($existente) {
                    $total = (int) $existente->cantidad + $cantidad;
                    $existente->update(array_merge(
                        ['cantidad' => $total],
                        $this->calcularLinea($precioNuevo, $total, $discriminaIgv)
                    ));
                } else {
                    $voucher->items()->create(array_merge([
                        'product_variant_id' => $nueva->id,
                        'cantidad' => $cantidad,
                        'precio_venta' => $precioNuevo,
                    ], $this->calcularLinea($precioNuevo, $cantidad, $discriminaIgv)));
                }

                // totales de la venta
                $restantes = $voucher->items()->get();
                $voucher->update([
                    'total' => $restantes->sum('subtotal'),
                    'subtotal' => $restantes->sum('valor_venta'),
                    'igv_total' => $restantes->sum('igv'),
                ]);

                // pagos: cobra la diferencia o devuelve el vuelto
                $metodo = null;

                if ($diferencia > 0) {
                    $metodo = $this->metodoDiferencia;
                    $pago = $voucher->payments()->where('metodo_pago', $metodo)->first();

                    if ($pago) {
                        $pago->update(['monto' => round((float) $pago->monto + $diferencia, 2)]);
                    } else {
                        $voucher->payments()->create(['metodo_pago' => $metodo, 'monto' => $diferencia]);
                    }
                } elseif ($diferencia < 0) {
                    $metodo = $this->descontarDePagos($voucher, abs($diferencia));
                }

                VoucherChange::create([
                    'business_location_id' => $voucher->business_location_id,
                    'voucher_id' => $voucher->id,
                    'user_id' => auth()->id(),
                    'tipo' => 'cambio',
                    'product_variant_id' => $item->product_variant_id,
                    'descripcion_anterior' => $descripcionAnterior,
                    'cantidad' => $cantidad,
                    'precio_anterior' => $precioAnterior,
                    'nuevo_product_variant_id' => $nueva->id,
                    'descripcion_nueva' => $descripcionNueva,
                    'precio_nuevo' => $precioNuevo,
                    'diferencia' => $diferencia,
                    'metodo_pago' => $metodo,
                    'motivo' => $this->motivo ?: null,
                ]);
            });
        } catch (\RuntimeException $e) {
            $this->addError('nuevaVariantId', $e->getMessage());
            return;
        }

        $this->cancelar();

        if ($diferencia > 0) {
            $mensaje = 'Cambio registrado. Cobra S/ ' . number_format($diferencia, 2) . ' de diferencia al cliente.';
        } elseif ($diferencia < 0) {
            $mensaje = 'Cambio registrado. Devuelve S/ ' . number_format(abs($diferencia), 2) . ' al cliente.';
        } else {
            $mensaje = 'Cambio registrado. Mismo precio: no se mueve dinero.';
        }

        session()->flash('ok', $mensaje);
    }

    // ───────────────────────── AUXILIARES ─────────────────────────

    // importe, valor de venta e IGV de una línea, igual que al registrar la venta
    protected function calcularLinea(float $precio, int $cantidad, bool $discriminaIgv): array
    {
        $importe = round($precio * $cantidad, 2);

        if ($discriminaIgv) {
            $valor = round($importe / 1.18, 2);
            $igv = round($importe - $valor, 2);
        } else {
            $valor = $importe;
            $igv = 0;
        }

        return ['valor_venta' => $valor, 'igv' => $igv, 'subtotal' => $importe];
    }

    // descuenta el monto de los pagos: primero el pago elegido, luego los demás de atrás hacia adelante
    protected function descontarDePagos($voucher, float $monto): ?string
    {
        $pagos = $voucher->payments()->get()->sortByDesc(
            fn($p) => $p->id == $this->pagoId ? PHP_INT_MAX : $p->id
        );

        $metodo = null;

        foreach ($pagos as $pago) {
            if ($monto <= 0) break;

            $quitar = min((float) $pago->monto, $monto);
            $metodo ??= $pago->metodo_pago;

            if ($quitar >= (float) $pago->monto) {
                $pago->delete();
            } else {
                $pago->update(['monto' => round((float) $pago->monto - $quitar, 2)]);
            }

            $monto = round($monto - $quitar, 2);
        }

        return $metodo;
    }

    public function render()
    {
        $items = VoucherItem::with(['variant.product', 'variant.size', 'voucher.payments'])
            ->whereHas('voucher', function ($q) {
                $q->where('business_location_id', session('sede_activa_id'))
                    ->whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin]);
            })
            ->orderBy('voucher_id')
            ->orderBy('id')
            ->get();

        // historial de cambios y anulaciones de la sede, dentro del mismo rango de fechas
        $historial = VoucherChange::with('user')
            ->where('business_location_id', session('sede_activa_id'))
            ->whereDate('created_at', '>=', $this->fecha_inicio)
            ->whereDate('created_at', '<=', $this->fecha_fin)
            ->latest()
            ->limit(100)
            ->get();

        $nuevaVariant = $this->nuevaVariantId
            ? ProductVariant::with(['product', 'size'])->find($this->nuevaVariantId)
            : null;

        return $this->view([
            'itemsPorFecha' => $items
                ->groupBy(fn($i) => $i->voucher->fecha->format('Y-m-d'))
                ->sortKeysDesc(),
            'nuevaVariant' => $nuevaVariant,
            'historial' => $historial,
        ]);
    }
};
