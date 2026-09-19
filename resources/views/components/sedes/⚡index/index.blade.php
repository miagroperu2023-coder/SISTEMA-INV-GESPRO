<div class="container py-4">

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold mb-0">Mis tiendas / sedes</h2>
        <button class="btn btn-primary" wire:click="abrirForm">+ Nueva sede</button>
    </div>

    @if ($mostrarForm)
        <div class="card mb-4">
            <div class="card-body">
                <h3 class="h6 fw-bold mb-3">Nueva sede</h3>

                <div class="mb-2">
                    <label class="form-label small">Nombre de la tienda</label>
                    <input type="text" wire:model="nombre" class="form-control form-control-sm"
                        placeholder="Ej: Tienda San Juan de Lurigancho">
                    @error('nombre')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-2">
                    <label class="form-label small">Dirección (opcional)</label>
                    <input type="text" wire:model="direccion" class="form-control form-control-sm">
                </div>

                <div class="mb-3">
                    <label class="form-label small">Teléfono (opcional)</label>
                    <input type="text" wire:model="telefono" class="form-control form-control-sm">
                </div>

                <button wire:click="guardar" class="btn btn-sm btn-primary">Guardar</button>
                <button wire:click="$set('mostrarForm', false)" class="btn btn-sm btn-link">Cancelar</button>
            </div>
        </div>
    @endif

    <table class="table table-sm align-middle">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Dirección</th>
                <th>Teléfono</th>
                <th>Cajas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sedes as $sede)
                <tr>
                    <td>{{ $sede->nombre }}</td>
                    <td>{{ $sede->direccion ?? '—' }}</td>
                    <td>{{ $sede->telefono ?? '—' }}</td>
                    <td>
                        @foreach ($sede->cashiers as $caja)
                            <span class="badge bg-light text-dark border me-1">{{ $caja->nombre }}</span>
                        @endforeach
                        <button class="btn btn-sm btn-outline-secondary"
                            wire:click="abrirFormCaja({{ $sede->id }})">+ Caja</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">Aún no tienes sedes.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($mostrarFormCaja)
        <div class="card mt-2" style="max-width: 500px;">
            <div class="card-body">
                <h3 class="h6 fw-bold mb-2">Nueva caja</h3>
                <div class="row g-2">
                    <div class="col-md-8">
                        <input type="text" wire:model="nombre_caja" class="form-control form-control-sm"
                            placeholder="Ej: Caja 2 - Mostrador zapatillas">
                        @error('nombre_caja')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <button wire:click="crearCaja" class="btn btn-sm btn-primary">Crear caja</button>
                        <button wire:click="$set('mostrarFormCaja', false)"
                            class="btn btn-sm btn-link">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <p class="small text-muted mt-3">Al crear una sede nueva, no se activa ninguna talla automáticamente — actívalas en
        la pantalla de "Tallas" según lo que vendas ahí. Si en una tienda trabajan 2 o más vendedoras cobrando al mismo
        tiempo, crea una caja adicional para cada una.</p>

</div>
