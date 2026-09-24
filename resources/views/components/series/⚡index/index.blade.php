<div class="container py-4">
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    <h2 class="h4 fw-bold mb-4">Series de comprobantes</h2>

    <p class="text-muted small">
        Coloca la serie y el <strong>último número ya usado</strong> (no el siguiente) para cada tipo de
        comprobante en esta sede. Si el negocio ya venía emitiendo con otro sistema, pon aquí el último
        número que emitieron y el sistema continuará automáticamente desde ahí.
    </p>

    @foreach (['boleta' => 'Boleta electrónica', 'factura' => 'Factura electrónica'] as $tipo => $label)
        <div class="card mb-3">
            <div class="card-body">
                <h3 class="h6 fw-bold mb-3">{{ $label }}</h3>
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small">Serie</label>
                        <input type="text" wire:model="series.{{ $tipo }}.serie"
                            class="form-control form-control-sm">
                        @error("series.$tipo.serie")
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Último número usado</label>
                        <input type="number" wire:model="series.{{ $tipo }}.correlativo_actual"
                            class="form-control form-control-sm">
                        @error("series.$tipo.correlativo_actual")
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <p class="small text-muted mb-1">
                            Próximo a emitir:
                            <strong>{{ $series[$tipo]['serie'] }}-{{ str_pad(($series[$tipo]['correlativo_actual'] ?? 0) + 1, 6, '0', STR_PAD_LEFT) }}</strong>
                        </p>
                        <button wire:click="guardar('{{ $tipo }}')"
                            class="btn btn-sm btn-primary">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
