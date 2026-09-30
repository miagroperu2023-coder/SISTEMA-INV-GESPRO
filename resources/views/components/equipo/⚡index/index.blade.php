<div class="container py-4">

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold mb-0">Mi equipo (vendedores)</h2>
        <button class="btn btn-primary" wire:click="abrirForm">+ Crear vendedor</button>
    </div>

    @if ($mostrarForm)
        <div class="card mb-4">
            <div class="card-body">
                <h3 class="h6 fw-bold mb-3">Nueva cuenta de vendedor</h3>

                <div class="mb-2">
                    <label class="form-label small">Nombre</label>
                    <input type="text" wire:model="name" class="form-control form-control-sm">
                    @error('name')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-2">
                    <label class="form-label small">Correo (esto será su usuario)</label>
                    <input type="email" wire:model="email" class="form-control form-control-sm">
                    @error('email')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-2">
                    <label class="form-label small">Contraseña que le vas a dar</label>
                    <input type="text" wire:model="password" class="form-control form-control-sm">
                    @error('password')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label small">¿A qué tienda tendrá acceso?</label>
                    <select wire:model="business_location_id" class="form-select form-select-sm">
                        <option value="">Selecciona</option>
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                        @endforeach
                    </select>
                    @error('business_location_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <button wire:click="crearVendedor" class="btn btn-sm btn-primary">Crear cuenta</button>
                <button wire:click="$set('mostrarForm', false)" class="btn btn-sm btn-link">Cancelar</button>
            </div>
        </div>
    @endif


    {{-- Vista de TABLA — solo visible en pantallas medianas hacia arriba --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Sede asignada</th>
                    <th>Estado</th>
                    <th style="width:320px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($vendedores as $vendedor)
                    @php $sede = $vendedor->sedesAsignadas->first(); @endphp
                    <tr wire:key="vendedor-desktop-{{ $vendedor->id }}">
                        <td>{{ $vendedor->name }}</td>
                        <td>{{ $vendedor->email }}</td>
                        <td>{{ $sede->nombre ?? '-' }}</td>
                        <td>
                            @if ($sede)
                                <span
                                    class="badge {{ $sede->pivot->estado === 'ACTIVO' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $sede->pivot->estado }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button wire:click="abrirEditarPassword({{ $vendedor->id }})"
                                    class="btn btn-sm btn-outline-primary">
                                    Cambiar contraseña
                                </button>
                                @if ($sede)
                                    <button wire:click="toggleEstado({{ $vendedor->id }}, {{ $sede->id }})"
                                        class="btn btn-sm {{ $sede->pivot->estado === 'ACTIVO' ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                        {{ $sede->pivot->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}
                                    </button>
                                @endif
                            </div>

                            @if ($editandoId === $vendedor->id)
                                <div class="mt-2 d-flex gap-2">
                                    <input type="text" wire:model="nuevaPassword"
                                        class="form-control form-control-sm" placeholder="Nueva contraseña"
                                        style="min-width: 160px;">
                                    <button wire:click="actualizarPassword({{ $vendedor->id }})"
                                        class="btn btn-sm btn-primary text-nowrap">Guardar</button>
                                </div>
                                @error('nuevaPassword')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Aún no has creado vendedores.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Vista de TARJETAS — solo visible en celular --}}
    <div class="d-md-none">
        @forelse ($vendedores as $vendedor)
            @php $sede = $vendedor->sedesAsignadas->first(); @endphp
            <div class="card mb-2" wire:key="vendedor-mobile-{{ $vendedor->id }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <strong>{{ $vendedor->name }}</strong>
                        @if ($sede)
                            <span
                                class="badge {{ $sede->pivot->estado === 'ACTIVO' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $sede->pivot->estado }}
                            </span>
                        @endif
                    </div>
                    <p class="small text-muted mb-1">{{ $vendedor->email }}</p>
                    <p class="small mb-3">Sede: {{ $sede->nombre ?? '-' }}</p>

                    <div class="d-flex flex-column gap-2">
                        <button wire:click="abrirEditarPassword({{ $vendedor->id }})"
                            class="btn btn-sm btn-outline-primary w-100">
                            Cambiar contraseña
                        </button>
                        @if ($sede)
                            <button wire:click="toggleEstado({{ $vendedor->id }}, {{ $sede->id }})"
                                class="btn btn-sm w-100 {{ $sede->pivot->estado === 'ACTIVO' ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                {{ $sede->pivot->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}
                            </button>
                        @endif
                    </div>

                    @if ($editandoId === $vendedor->id)
                        <div class="mt-2 d-flex flex-column gap-2">
                            <input type="text" wire:model="nuevaPassword" class="form-control form-control-sm w-100"
                                placeholder="Nueva contraseña">
                            <button wire:click="actualizarPassword({{ $vendedor->id }})"
                                class="btn btn-sm btn-primary w-100">Guardar</button>
                        </div>
                        @error('nuevaPassword')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    @endif
                </div>
            </div>
        @empty
            <p class="text-center text-muted">Aún no has creado vendedores.</p>
        @endforelse
    </div>


    <p class="small text-muted mt-2">Comparte el correo y la contraseña con tu vendedor por WhatsApp o el medio que
        prefieras — con eso entra directo a su tienda asignada.</p>

</div>
