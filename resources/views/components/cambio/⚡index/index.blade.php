<div class="container py-4">

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <h2 class="h4 fw-bold mb-4">Cambios y anulaciones</h2>

    <div class="row g-2 mb-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Fecha inicial</label>
            <input type="date" wire:model.live="fecha_inicio" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Fecha final</label>
            <input type="date" wire:model.live="fecha_fin" class="form-control form-control-sm">
        </div>
    </div>

    <div class="accordion mb-4">
        @forelse ($itemsPorFecha as $fecha => $items)
            @php
                $dia = \Carbon\Carbon::parse($fecha);
                $ventasDelDia = $items->pluck('voucher_id')->unique()->count();
            @endphp

            <div class="accordion-item" wire:key="dia-{{ $fecha }}">
                <h2 class="accordion-header">
                    <button wire:ignore.self class="accordion-button {{ $loop->first ? '' : 'collapsed' }}"
                        type="button" data-bs-toggle="collapse" data-bs-target="#dia-{{ $fecha }}">
                        <div class="d-flex w-100 justify-content-between align-items-center me-3 flex-wrap gap-1">
                            <strong>{{ ucfirst($dia->locale('es')->isoFormat('dddd DD/MM/YYYY')) }}</strong>
                            <span class="text-muted small">{{ $ventasDelDia }} venta(s)</span>
                            <strong>S/ {{ number_format($items->sum('subtotal'), 2) }}</strong>
                        </div>
                    </button>
                </h2>

                <div wire:ignore.self id="dia-{{ $fecha }}"
                    class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}">
                    <div class="accordion-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Comprobante</th>
                                        <th>Producto</th>
                                        <th>Talla</th>
                                        <th>Color</th>
                                        <th>Cant.</th>
                                        <th>P. Venta</th>
                                        <th>Subtotal</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $fila)
                                        @php
                                            $primera =
                                                $loop->first ||
                                                $items[$loop->index - 1]->voucher_id !== $fila->voucher_id;
                                        @endphp
                                        <tr wire:key="item-{{ $fila->id }}"
                                            class="{{ $primera && !$loop->first ? 'border-top border-2 border-secondary' : '' }}">
                                            <td>{{ $loop->iteration }}.</td>
                                            <td>{{ strtoupper($fila->voucher->tipo_comprobante) }}</td>
                                            <td>{{ $fila->variant->product->nombre }}</td>
                                            <td>{{ $fila->variant->size->valor }}</td>
                                            <td>{{ $fila->variant->color }}</td>
                                            <td>{{ $fila->cantidad }}</td>
                                            <td>S/ {{ number_format($fila->precio_venta, 2) }}</td>
                                            <td>S/ {{ number_format($fila->subtotal, 2) }}</td>
                                            <td>
                                                @if ($fila->voucher->tipo_comprobante === 'ticket')
                                                    <div class="d-flex gap-1">
                                                        <button wire:click="abrirCambio({{ $fila->id }})"
                                                            class="btn btn-sm btn-outline-primary">Cambiar</button>
                                                        <button wire:click="abrirAnular({{ $fila->id }})"
                                                            class="btn btn-sm btn-outline-danger">Anular</button>
                                                    </div>
                                                @else
                                                    <span class="small text-muted">Nota de crédito</span>
                                                @endif
                                            </td>
                                        </tr>

                                        {{-- ───────── Panel de ANULACIÓN ───────── --}}
                                        @if ($anulandoItemId == $fila->id)
                                            @php
                                                $pagos = $fila->voucher->payments;
                                                $unicaLinea =
                                                    $items->where('voucher_id', $fila->voucher_id)->count() === 1;
                                            @endphp
                                            <tr wire:key="panel-{{ $fila->id }}">
                                                <td colspan="9" class="bg-light">
                                                    <div class="p-2">
                                                        <p class="fw-bold mb-2">
                                                            Anular: {{ $fila->variant->product->nombre }} —
                                                            {{ $fila->variant->color }} —
                                                            {{ $fila->variant->size->valor }}
                                                        </p>

                                                        <div class="row g-2 align-items-start">
                                                            <div class="col-md-2">
                                                                <label class="form-label small mb-0">Unidades a
                                                                    anular</label>
                                                                <input type="number" min="1"
                                                                    max="{{ $fila->cantidad }}"
                                                                    wire:model.live="cantidadAnular"
                                                                    class="form-control form-control-sm">
                                                                @error('cantidadAnular')
                                                                    <div class="text-danger small">{{ $message }}
                                                                    </div>
                                                                @enderror
                                                            </div>

                                                            @if ($pagos->count() > 1)
                                                                <div class="col-md-3">
                                                                    <label class="form-label small mb-0">¿De qué pago se
                                                                        descuenta?</label>
                                                                    <select wire:model="pagoId"
                                                                        class="form-select form-select-sm">
                                                                        <option value="">Selecciona</option>
                                                                        @foreach ($pagos as $pago)
                                                                            <option value="{{ $pago->id }}">
                                                                                {{ ucfirst($pago->metodo_pago) }} — S/
                                                                                {{ number_format($pago->monto, 2) }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error('pagoId')
                                                                        <div class="text-danger small">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </div>
                                                            @endif

                                                            <div class="col-md-4">
                                                                <label class="form-label small mb-0">Motivo
                                                                    (opcional)
                                                                </label>
                                                                <input type="text" wire:model="motivo"
                                                                    class="form-control form-control-sm"
                                                                    placeholder="Ej: digitado dos veces">
                                                            </div>

                                                            <div class="col-md-3 pt-md-4">
                                                                <button wire:click="confirmarAnulacion"
                                                                    class="btn btn-sm btn-danger">Confirmar</button>
                                                                <button wire:click="cancelar"
                                                                    class="btn btn-sm btn-link">Cancelar</button>
                                                            </div>
                                                        </div>

                                                        <p class="small text-muted mt-2 mb-0">
                                                            Se devuelve al stock {{ (int) $cantidadAnular }} unidad(es)
                                                            y se
                                                            descuenta S/
                                                            {{ number_format($fila->precio_venta * (int) $cantidadAnular, 2) }}
                                                            de la venta.
                                                            @if ($unicaLinea && (int) $cantidadAnular === (int) $fila->cantidad)
                                                                <strong class="text-danger">Es el único producto: se
                                                                    anulará la venta completa.</strong>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif

                                        {{-- ───────── Panel de CAMBIO ───────── --}}
                                        @if ($cambiandoItemId == $fila->id)
                                            @php
                                                $pagos = $fila->voucher->payments;
                                                $cant = max(1, min((int) $cantidadCambio, (int) $fila->cantidad));
                                                $precioEfectivo = is_numeric($precioNuevo)
                                                    ? (float) $precioNuevo
                                                    : ($nuevaVariant
                                                        ? (float) $nuevaVariant->precio_venta
                                                        : 0);
                                                $diferencia = $nuevaVariant
                                                    ? round(($precioEfectivo - (float) $fila->precio_venta) * $cant, 2)
                                                    : null;
                                            @endphp
                                            <tr wire:key="panel-cambio-{{ $fila->id }}">
                                                <td colspan="9" class="bg-light">
                                                    <div class="p-2">
                                                        <p class="fw-bold mb-2">
                                                            Cambiar: {{ $fila->variant->product->nombre }} —
                                                            {{ $fila->variant->color }} —
                                                            {{ $fila->variant->size->valor }}
                                                            (S/ {{ number_format($fila->precio_venta, 2) }} c/u)
                                                        </p>

                                                        <div class="row g-2 align-items-start">
                                                            <div class="col-md-2">
                                                                <label class="form-label small mb-0">Unidades que
                                                                    devuelve</label>
                                                                <input type="number" min="1"
                                                                    max="{{ $fila->cantidad }}"
                                                                    wire:model.live="cantidadCambio"
                                                                    class="form-control form-control-sm">
                                                                @error('cantidadCambio')
                                                                    <div class="text-danger small">{{ $message }}
                                                                    </div>
                                                                @enderror
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label small mb-0">Prenda
                                                                    nueva</label>

                                                                @if ($nuevaVariant)
                                                                    <div
                                                                        class="d-flex justify-content-between align-items-center border rounded bg-white px-2 py-1 small">
                                                                        <span>
                                                                            {{ $nuevaVariant->product->nombre }} —
                                                                            {{ $nuevaVariant->color }} —
                                                                            {{ $nuevaVariant->size->valor }}
                                                                            · Lista: S/
                                                                            {{ number_format($nuevaVariant->precio_venta, 2) }}
                                                                            · Stock: {{ $nuevaVariant->stock }}
                                                                        </span>
                                                                        <button type="button" wire:click="quitarNueva"
                                                                            class="btn btn-sm btn-link py-0">Quitar</button>
                                                                    </div>

                                                                    <div class="mt-2" style="max-width: 220px;">
                                                                        <label class="form-label small mb-0">Precio de
                                                                            venta (editable)</label>
                                                                        <div class="input-group input-group-sm">
                                                                            <span class="input-group-text">S/</span>
                                                                            <input type="number" step="0.01"
                                                                                min="0.01" inputmode="decimal"
                                                                                wire:model.live.debounce.400ms="precioNuevo"
                                                                                class="form-control">
                                                                        </div>
                                                                        @error('precioNuevo')
                                                                            <div class="text-danger small">
                                                                                {{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                @else
                                                                    <input type="text"
                                                                        wire:model.live.debounce.300ms="busquedaNueva"
                                                                        class="form-control form-control-sm"
                                                                        placeholder="Busca por nombre, color o talla..."
                                                                        onkeydown="if(event.key === 'Enter'){ event.preventDefault(); return false; }">

                                                                    @if (count($resultadosNuevos) > 0)
                                                                        <div class="list-group mt-1"
                                                                            style="max-height: 220px; overflow-y: auto;">
                                                                            @foreach ($resultadosNuevos as $v)
                                                                                <button type="button"
                                                                                    wire:key="nueva-{{ $v->id }}"
                                                                                    wire:click="seleccionarNueva({{ $v->id }})"
                                                                                    class="list-group-item list-group-item-action d-flex justify-content-between small py-1">
                                                                                    <span>{{ $v->product->nombre }} —
                                                                                        {{ $v->color }} —
                                                                                        {{ $v->size->valor }}</span>
                                                                                    <span class="text-muted">Stock:
                                                                                        {{ $v->stock }} · S/
                                                                                        {{ number_format($v->precio_venta, 2) }}</span>
                                                                                </button>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                @endif

                                                                @error('nuevaVariantId')
                                                                    <div class="text-danger small">{{ $message }}
                                                                    </div>
                                                                @enderror
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label class="form-label small mb-0">Motivo
                                                                    (opcional)</label>
                                                                <input type="text" wire:model="motivo"
                                                                    class="form-control form-control-sm"
                                                                    placeholder="Ej: talla muy chica">
                                                            </div>
                                                        </div>

                                                        @if ($diferencia !== null && $diferencia > 0)
                                                            <div class="row g-2 mt-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label small mb-0">
                                                                        El cliente paga S/
                                                                        {{ number_format($diferencia, 2) }} con
                                                                    </label>
                                                                    <select wire:model="metodoDiferencia"
                                                                        class="form-select form-select-sm">
                                                                        <option value="efectivo">Efectivo</option>
                                                                        <option value="yape">Yape</option>
                                                                        <option value="plin">Plin</option>
                                                                        <option value="tarjeta">Tarjeta</option>
                                                                    </select>
                                                                    @error('metodoDiferencia')
                                                                        <div class="text-danger small">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        @elseif ($diferencia !== null && $diferencia < 0 && $pagos->count() > 1)
                                                            <div class="row g-2 mt-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label small mb-0">¿De qué pago
                                                                        se
                                                                        devuelve el dinero?</label>
                                                                    <select wire:model="pagoId"
                                                                        class="form-select form-select-sm">
                                                                        <option value="">Selecciona</option>
                                                                        @foreach ($pagos as $pago)
                                                                            <option value="{{ $pago->id }}">
                                                                                {{ ucfirst($pago->metodo_pago) }} — S/
                                                                                {{ number_format($pago->monto, 2) }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error('pagoId')
                                                                        <div class="text-danger small">{{ $message }}
                                                                        </div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <div class="mt-2">
                                                            <button wire:click="confirmarCambio"
                                                                wire:loading.attr="disabled"
                                                                class="btn btn-sm btn-primary">Confirmar
                                                                cambio</button>
                                                            <button wire:click="cancelar"
                                                                class="btn btn-sm btn-link">Cancelar</button>
                                                        </div>

                                                        <p class="small text-muted mt-2 mb-0">
                                                            Vuelve al stock {{ $cant }} unidad(es) de la prenda
                                                            devuelta.
                                                            @if ($nuevaVariant)
                                                                Sale del stock {{ $cant }} unidad(es) de la
                                                                prenda
                                                                nueva.
                                                                @if ($diferencia > 0)
                                                                    <strong class="text-success">Más cara: el cliente
                                                                        paga S/ {{ number_format($diferencia, 2) }}
                                                                        de diferencia.</strong>
                                                                @elseif ($diferencia < 0)
                                                                    <strong class="text-danger">Más barata: se le
                                                                        devuelve S/
                                                                        {{ number_format(abs($diferencia), 2) }}.</strong>
                                                                @else
                                                                    <strong>Mismo precio: solo se entrega la nueva
                                                                        prenda.</strong>
                                                                @endif
                                                            @endif
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center text-muted py-3">No hay ventas en ese rango de fechas.</p>
        @endforelse
    </div>

    {{-- ───────── Historial de cambios y anulaciones ───────── --}}
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-1 mb-2">
        <h3 class="h5 fw-bold mb-0">Historial de cambios y anulaciones</h3>
        @if ($historial->isNotEmpty())
            @php $netoCambios = $historial->where('tipo', 'cambio')->sum('diferencia'); @endphp
            <span class="small text-muted">
                {{ $historial->count() }} registro(s) ·
                Diferencia neta de cambios:
                <strong class="{{ $netoCambios < 0 ? 'text-danger' : 'text-success' }}">
                    {{ $netoCambios < 0 ? '−' : '+' }}S/ {{ number_format(abs($netoCambios), 2) }}
                </strong>
            </span>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-striped align-middle">
            <thead>
                <tr>
                    <th>Fecha y hora</th>
                    <th>Tipo</th>
                    <th>Ticket</th>
                    <th>Sale (devuelta / anulada)</th>
                    <th>Entra (prenda nueva)</th>
                    <th class="text-end">Diferencia</th>
                    <th>Método</th>
                    <th>Motivo</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($historial as $c)
                    <tr wire:key="hist-{{ $c->id }}">
                        <td class="text-nowrap">{{ $c->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @switch($c->tipo)
                                @case('cambio')
                                    <span class="badge bg-primary">Cambio</span>
                                @break

                                @case('anulacion_producto')
                                    <span class="badge bg-warning text-dark">Anulación de producto</span>
                                @break

                                @case('anulacion_venta')
                                    <span class="badge bg-danger">Venta anulada</span>
                                @break
                            @endswitch
                        </td>
                        <td>#{{ $c->voucher_id }}</td>
                        <td>
                            {{ $c->descripcion_anterior }}
                            <div class="small text-muted">
                                {{ $c->cantidad }} u. · S/ {{ number_format($c->precio_anterior, 2) }} c/u
                            </div>
                        </td>
                        <td>
                            @if ($c->tipo === 'cambio')
                                {{ $c->descripcion_nueva }}
                                <div class="small text-muted">
                                    {{ $c->cantidad }} u. · S/ {{ number_format($c->precio_nuevo, 2) }} c/u
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @if ($c->diferencia > 0)
                                <span class="text-success fw-bold">+S/ {{ number_format($c->diferencia, 2) }}</span>
                            @elseif ($c->diferencia < 0)
                                <span class="text-danger fw-bold">−S/
                                    {{ number_format(abs($c->diferencia), 2) }}</span>
                            @else
                                <span class="text-muted">S/ 0.00</span>
                            @endif
                        </td>
                        <td>{{ $c->metodo_pago ? ucfirst($c->metodo_pago) : '—' }}</td>
                        <td class="small">{{ $c->motivo ?: '—' }}</td>
                        <td class="small">{{ $c->user->name ?? '—' }}</td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">
                                No hay cambios ni anulaciones en ese rango de fechas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($historial->count() >= 100)
            <p class="small text-muted">Se muestran los últimos 100 registros del rango. Acorta las fechas para ver otros.
            </p>
        @endif
    </div>
