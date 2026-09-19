<div class="container py-4">
    <h2 class="h4 fw-bold mb-4">Control de caja</h2>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if (!$turnoAbierto)
        <div class="card">
            <div class="card-body">
                <h3 class="h6 fw-bold mb-3">Abrir turno</h3>

                @if ($cajas->isEmpty())
                    <div class="alert alert-warning">
                        No hay cajas disponibles en este momento — todas están siendo usadas por otras personas.
                    </div>
                @else
                    <div class="mb-2">
                        <label class="form-label small">Caja</label>
                        <select wire:model="cashier_id" class="form-select form-select-sm">
                            <option value="">Selecciona</option>
                            @foreach ($cajas as $caja)
                                <option value="{{ $caja->id }}">{{ $caja->nombre }}</option>
                            @endforeach
                        </select>
                        @error('cashier_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Monto de apertura (efectivo)</label>
                        <input type="number" step="0.01" wire:model="monto_apertura"
                            class="form-control form-control-sm">
                        @error('monto_apertura')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <button wire:click="abrirTurno" class="btn btn-primary btn-sm">Abrir caja</button>
                @endif

                @if ($cajasOcupadas->isNotEmpty())
                    <hr>
                    <p class="small text-muted mb-1">Cajas en uso ahora mismo:</p>
                    @foreach ($cajasOcupadas as $ocupada)
                        <div class="small text-muted">
                            🔒 {{ $ocupada['nombre'] }} — abierta por {{ $ocupada['usuario'] }} desde las
                            {{ $ocupada['desde']->format('H:i') }}
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <p class="mb-1"><strong>Abierta desde:</strong> {{ $turnoAbierto->abierta_at->format('d/m/Y H:i') }}
                </p>
                <p class="mb-3"><strong>Apertura:</strong> S/ {{ number_format($turnoAbierto->monto_apertura, 2) }}
                </p>
                <h3 class="h6 fw-bold mb-2">Cerrar turno</h3>
                <div class="mb-2">
                    <label class="form-label small">Monto contado físicamente</label>
                    <input type="number" step="0.01" wire:model="monto_cierre_real"
                        class="form-control form-control-sm">
                    @error('monto_cierre_real')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <button wire:click="cerrarTurno" class="btn btn-danger btn-sm">Cerrar caja</button>
            </div>
        </div>
    @endif
</div>
