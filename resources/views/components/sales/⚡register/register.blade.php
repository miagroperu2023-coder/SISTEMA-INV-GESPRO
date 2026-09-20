<div class="container py-4">

    @if (!session()->has('cashier_shift_id'))
        <div class="alert alert-warning d-flex justify-content-between align-items-center">
            <span>No tienes una caja abierta.</span>
            <a href="{{ route('caja.index') }}" class="btn btn-sm btn-warning">Abrir caja</a>
        </div>
    @else
        @if (session('ok'))
            <div class="alert alert-success">{{ session('ok') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h2 class="h4 fw-bold mb-4">Registrar venta</h2>

        <div class="position-relative mb-3">
            <input type="text" wire:model.live.debounce.300ms="busqueda" class="form-control"
                placeholder="Busca por nombre o color..." autofocus>

            @if (count($resultados) > 0)
                <div class="list-group position-absolute w-100 shadow" style="z-index: 10;">
                    @foreach ($resultados as $variant)
                        <button type="button" wire:click="agregarItem({{ $variant->id }})"
                            class="list-group-item list-group-item-action d-flex justify-content-between">
                            <span>{{ $variant->product->nombre }} — {{ $variant->color }} —
                                {{ $variant->size->valor }}</span>
                            <span class="text-muted small">Stock: {{ $variant->stock }} · S/
                                {{ number_format($variant->precio_venta, 2) }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        @if (count($items) > 0)
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Talla</th>
                            <th>Color</th>
                            <th style="width:90px">Cant.</th>
                            <th style="width:110px">Precio</th>
                            <th style="width:100px">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $index => $item)
                            <tr wire:key="item-{{ $item['variant_id'] }}">
                                <td>{{ $item['nombre'] }}</td>
                                <td>{{ $item['talla'] }}</td>
                                <td>{{ $item['color'] }}</td>
                                <td>
                                    <input type="number" min="1" inputmode="numeric"
                                        wire:model.live="items.{{ $index }}.cantidad"
                                        class="form-control form-control-sm">
                                </td>
                                <td>
                                    <input type="number" step="0.01" inputmode="decimal"
                                        wire:model.live="items.{{ $index }}.precio_venta"
                                        class="form-control form-control-sm">
                                </td>
                                <td>S/ {{ number_format($item['precio_venta'] * $item['cantidad'], 2) }}</td>
                                <td>
                                    <button wire:click="quitarItem({{ $index }})"
                                        class="btn btn-sm btn-outline-danger">×</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mb-3">
                <label class="form-label small">¿Qué comprobante necesita?</label>
                <select wire:model.live="tipo_comprobante" class="form-select form-select-sm">
                    @foreach ($comprobantesPermitidos as $tipo)
                        <option value="{{ $tipo }}">
                            @switch($tipo)
                                @case('ticket')
                                    Solo venta (sin comprobante SUNAT)
                                @break

                                @case('boleta')
                                    Boleta electrónica
                                @break

                                @case('factura')
                                    Factura electrónica
                                @break
                            @endswitch
                        </option>
                    @endforeach
                </select>

                @if ($tipo_comprobante !== 'ticket')
                    @if ($discriminaIgv)
                        <p class="small text-muted mt-1">Se desglosará IGV (18%) en el comprobante.</p>
                    @else
                        <p class="small text-muted mt-1">Comprobante sin desglose de IGV (régimen NRUS).</p>
                    @endif
                @endif
            </div>

            @if ($tipo_comprobante !== 'ticket')
                <div class="mb-3 position-relative">
                    <label class="form-label small">Cliente</label>
                    <input type="text" wire:model.live.debounce.300ms="buscarCliente"
                        class="form-control form-control-sm" placeholder="DNI/RUC o nombre">

                    @if (count($clientesEncontrados) > 0)
                        <div class="list-group position-absolute w-100 shadow" style="z-index: 10;">
                            @foreach ($clientesEncontrados as $cliente)
                                <button type="button" wire:click="seleccionarCliente({{ $cliente->id }})"
                                    class="list-group-item list-group-item-action">
                                    {{ $cliente->nombre_razon_social }} — {{ $cliente->numero_documento }}
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @if ($customer_id)
                        <p class="small text-success mt-1">Cliente seleccionado ✓</p>
                    @endif
                </div>
            @endif

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label small mb-0">Pagos</label>
                    <button wire:click="agregarPago" class="btn btn-sm btn-outline-secondary">+ Agregar pago</button>
                </div>
                @foreach ($pagos as $index => $pago)
                    <div class="row g-2 mb-1 align-items-center" wire:key="pago-{{ $index }}">
                        <div class="col-5">
                            <select wire:model="pagos.{{ $index }}.metodo_pago"
                                class="form-select form-select-sm">
                                <option value="yape">Yape</option>
                                <option value="plin">Plin</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <input type="number" step="0.01" inputmode="decimal"
                                wire:model="pagos.{{ $index }}.monto" class="form-control form-control-sm"
                                placeholder="Monto">
                        </div>
                        <div class="col-2">
                            <button wire:click="completarMonto({{ $index }})"
                                class="btn btn-sm btn-outline-primary" title="Completar con lo que falta">✓</button>
                        </div>
                        <div class="col-1">
                            <button wire:click="quitarPago({{ $index }})"
                                class="btn btn-sm btn-outline-danger">×</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Total: S/ {{ number_format($this->total, 2) }}</h5>
                    <small class="text-muted">Pagado: S/ {{ number_format($this->totalPagado, 2) }}</small>
                </div>
                <button wire:click="guardarVenta" class="btn btn-primary">Guardar venta</button>
            </div>
        @else
            <p class="text-muted">Busca un producto para empezar.</p>
        @endif
    @endif

</div>
