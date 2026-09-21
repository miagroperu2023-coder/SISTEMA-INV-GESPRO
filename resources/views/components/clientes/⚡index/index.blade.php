<div class="container py-4">

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold mb-0">Clientes</h2>
        <button class="btn btn-primary" wire:click="abrirForm">+ Nuevo cliente</button>
    </div>

    @if ($mostrarForm)
        <div class="card mb-4">
            <div class="card-body">
                <h3 class="h6 fw-bold mb-3">Registrar cliente</h3>

                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <label class="form-label small">Tipo</label>
                        <select wire:model="tipo_documento" class="form-select form-select-sm">
                            <option value="dni">DNI (persona)</option>
                            <option value="ruc">RUC (empresa)</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small">Número de documento</label>
                        <input type="text" wire:model="numero_documento" inputmode="numeric"
                            maxlength="{{ $tipo_documento === 'dni' ? 8 : 11 }}" class="form-control form-control-sm">
                        @error('numero_documento')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button wire:click="buscarEnApi" wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline-primary w-100">
                            <span wire:loading.remove wire:target="buscarEnApi">
                                Buscar en {{ $tipo_documento === 'dni' ? 'RENIEC' : 'SUNAT' }}
                            </span>
                            <span wire:loading wire:target="buscarEnApi">Buscando...</span>
                        </button>
                    </div>
                </div>

                @if ($mensajeApi)
                    <div class="alert alert-warning py-2 small">{{ $mensajeApi }}</div>
                @endif

                <div class="mb-2">
                    <label
                        class="form-label small">{{ $tipo_documento === 'dni' ? 'Nombres y apellidos' : 'Razón social' }}</label>
                    <input type="text" wire:model="nombre_razon_social" class="form-control form-control-sm">
                    @error('nombre_razon_social')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label small">Dirección (opcional)</label>
                    <input type="text" wire:model="direccion" class="form-control form-control-sm">
                </div>

                <button wire:click="guardar" class="btn btn-sm btn-primary">Guardar cliente</button>
                <button wire:click="$set('mostrarForm', false)" class="btn btn-sm btn-link">Cancelar</button>
            </div>
        </div>
    @endif

    <input type="text" wire:model.live.debounce.300ms="buscar" class="form-control mb-3"
        placeholder="🔍 Buscar por nombre o documento...">

    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Documento</th>
                    <th>Nombre / Razón social</th>
                    <th>Dirección</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clientes as $cliente)
                    <tr>
                        <td>{{ strtoupper($cliente->tipo_documento) }}</td>
                        <td>{{ $cliente->numero_documento }}</td>
                        <td>{{ $cliente->nombre_razon_social }}</td>
                        <td>{{ $cliente->direccion ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Aún no hay clientes registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
