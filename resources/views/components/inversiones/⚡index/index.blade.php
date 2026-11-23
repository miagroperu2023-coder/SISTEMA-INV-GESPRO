<div class="container py-4">

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 fw-bold mb-0">Inversiones</h2>
        <button class="btn btn-primary" wire:click="abrirForm">+ Nueva inversión</button>
    </div>

    @if ($mostrarForm)
        <div class="card mb-4">
            <div class="card-body">
                <h3 class="h6 fw-bold mb-3">Registrar inversión</h3>

                <div class="row g-2 mb-2">
                    <div class="col-6 col-md-3">
                        <label class="form-label small">Fecha</label>
                        <input type="date" wire:model="fecha" class="form-control form-control-sm">
                        @error('fecha') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small">Total invertido</label>
                        <input type="number" step="0.01" inputmode="decimal"
                            wire:model.live.debounce.300ms="total"
                            class="form-control form-control-sm" placeholder="Ej: 800">
                        @error('total') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small">¿Qué compraste?</label>
                        <input type="text" list="lista-conceptos" wire:model="concepto"
                            class="form-control form-control-sm" placeholder="Ej: Jeans, Blusas, Polos...">
                        <datalist id="lista-conceptos">
                            @foreach ($conceptos as $c)
                                <option value="{{ $c }}">
                            @endforeach
                        </datalist>
                        @error('concepto') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-5">
                        <label class="form-label small">Proveedor (opcional)</label>
                        <input type="text" wire:model="proveedor" class="form-control form-control-sm"
                            placeholder="Ej: Galería X - Gamarra">
                    </div>
                    <div class="col-12 col-md-7">
                        <label class="form-label small">Notas (opcional)</label>
                        <input type="text" wire:model="notas" class="form-control form-control-sm">
                    </div>
                </div>

                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small mb-0">¿Cómo pagaste?</label>
                        <button wire:click="agregarPago" class="btn btn-sm btn-outline-secondary">+ Agregar pago</button>
                    </div>

                    @foreach ($pagos as $index => $pago)
                        <div class="row g-2 mb-1 align-items-center" wire:key="inv-pago-{{ $index }}">
                            <div class="col-5">
                                <select wire:model="pagos.{{ $index }}.metodo_pago" class="form-select form-select-sm">
                                    <option value="yape">Yape</option>
                                    <option value="plin">Plin</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <input type="number" step="0.01" inputmode="decimal"
                                    wire:model.live.debounce.300ms="pagos.{{ $index }}.monto"
                                    class="form-control form-control-sm" placeholder="Monto">
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

                    @error('pagos.*.monto') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                @php $falta = round((float) $total - $this->totalPagado, 2); @endphp
                <div class="d-flex justify-content-between small mb-3">
                    <span class="text-muted">Pagado: S/ {{ number_format($this->totalPagado, 2) }}</span>
                    <span class="{{ $falta == 0 ? 'text-success' : 'text-danger' }}">
                        {{ $falta == 0 ? 'Cuadra ✓' : 'Falta: S/ ' . number_format($falta, 2) }}
                    </span>
                </div>

                <button wire:click="guardar" class="btn btn-sm btn-primary">Guardar inversión</button>
                <button wire:click="$set('mostrarForm', false)" class="btn btn-sm btn-link">Cancelar</button>
            </div>
        </div>
    @endif

    {{-- Filtro de fechas --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <label class="form-label small">Desde</label>
            <input type="date" wire:model.live="fecha_inicio" class="form-control form-control-sm">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small">Hasta</label>
            <input type="date" wire:model.live="fecha_fin" class="form-control form-control-sm">
        </div>
    </div>

    {{-- Resumen --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="small text-muted">Total invertido en el período</div>
                    <div class="h4 fw-bold mb-0">S/ {{ number_format($totalInvertido, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="small text-muted mb-1">Por método de pago</div>
                    @forelse ($porMetodo as $metodo => $monto)
                        <div class="d-flex justify-content-between">
                            <span class="text-capitalize">{{ $metodo }}</span>
                            <span>S/ {{ number_format($monto, 2) }}</span>
                        </div>
                    @empty
                        <span class="small text-muted">Sin inversiones en este período.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Listado --}}
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Concepto</th>
                    <th>Pagos</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inversiones as $inv)
                    <tr wire:key="inv-{{ $inv->id }}" class="{{ $inv->estado === 'ANULADO' ? 'opacity-50' : '' }}">
                        <td>{{ $inv->fecha->format('d/m/Y') }}</td>
                        <td>
                            {{ $inv->concepto }}
                            @if ($inv->estado === 'ANULADO')
                                <span class="badge bg-secondary">ANULADA</span>
                            @endif
                            @if ($inv->proveedor)
                                <div class="small text-muted">{{ $inv->proveedor }}</div>
                            @endif
                        </td>
                        <td class="small">
                            @foreach ($inv->payments as $p)
                                <div class="text-capitalize">{{ $p->metodo_pago }}: S/ {{ number_format($p->monto, 2) }}</div>
                            @endforeach
                        </td>
                        <td>S/ {{ number_format($inv->total, 2) }}</td>
                        <td>
                            <button wire:click="cambiarEstado({{ $inv->id }})"
                                wire:confirm="¿Seguro que quieres {{ $inv->estado === 'ACTIVO' ? 'anular' : 'restaurar' }} esta inversión?"
                                class="btn btn-sm btn-outline-{{ $inv->estado === 'ACTIVO' ? 'danger' : 'success' }}">
                                {{ $inv->estado === 'ACTIVO' ? 'Anular' : 'Restaurar' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No hay inversiones en este período.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>