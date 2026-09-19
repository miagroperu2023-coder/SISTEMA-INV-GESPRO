<div class="container py-4">

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold mb-0">Tallas que manejo en mi tienda</h2>
        <button class="btn btn-primary btn-sm" wire:click="abrirForm">+ Crear talla nueva</button>
    </div>

    @if ($mostrarForm)
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small">Tipo</label>
                        <select wire:model="nuevo_tipo" class="form-select form-select-sm">
                            <option value="letra">Letra (XS, S, M...)</option>
                            <option value="numero">Número (26, 28...)</option>
                            <option value="calzado">Calzado (35, 36...)</option>
                            <option value="unico">Único / sin talla</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Valor</label>
                        <input type="text" wire:model="nuevo_valor" class="form-control form-control-sm"
                            placeholder="Ej: 3XL, 45, ÚNICA">
                        @error('nuevo_valor')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button wire:click="crearTalla" class="btn btn-sm btn-primary me-2">Crear y activar</button>
                        <button wire:click="$set('mostrarForm', false)" class="btn btn-sm btn-link">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <p class="text-muted small mb-4">Activa solo los tipos de talla que usas. Las que desactives dejarán de aparecer al
        crear productos nuevos.</p>

    @foreach ($todasLasTallas as $tipo => $tallas)
        <div class="card mb-3">
            <div class="card-body">
                <h3 class="h6 fw-bold text-uppercase text-muted mb-3">{{ $tipo }}</h3>
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($tallas as $size)
                        @php $activa = ($estadosPorTalla[$size->id] ?? 'INACTIVO') === 'ACTIVO'; @endphp
                        <button type="button" wire:click="toggle({{ $size->id }})"
                            class="btn btn-sm {{ $activa ? 'btn-success' : 'btn-outline-secondary' }}"
                            wire:key="talla-toggle-{{ $size->id }}-{{ $activa ? 'on' : 'off' }}">
                            {{ $size->valor }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
