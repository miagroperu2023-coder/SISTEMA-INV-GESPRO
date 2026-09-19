<div class="container py-4">

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    <h2 class="h4 fw-bold mb-4">Categorías</h2>

    <div class="card mb-4">
        <div class="card-body">
            <h3 class="h6 fw-bold mb-3">{{ $categoria_id_editando ? 'Editar categoría' : 'Nueva categoría' }}</h3>

            <div class="row g-2">
                <div class="col-md-8">
                    <input type="text" wire:model="nombre" class="form-control form-control-sm"
                        placeholder="Ej: Pantalón, Superior, Zapatillas...">
                    @error('nombre')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <button wire:click="guardar" class="btn btn-primary btn-sm">
                        {{ $categoria_id_editando ? 'Actualizar' : 'Guardar' }}
                    </button>
                    @if ($categoria_id_editando)
                        <button wire:click="cancelarEdicion" class="btn btn-link btn-sm">Cancelar</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <table class="table table-sm align-middle">
        <thead>
            <tr>
                <th>Nombre</th>
                <th style="width:120px">Estado</th>
                <th style="width:180px"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categorias as $categoria)
                <tr wire:key="categoria-{{ $categoria->id }}">
                    <td>{{ $categoria->nombre }}</td>
                    <td>
                        <span class="badge {{ $categoria->estado === 'ACTIVO' ? 'bg-success' : 'bg-secondary' }}">
                            {{ $categoria->estado }}
                        </span>
                    </td>
                    <td>
                        <button wire:click="editar({{ $categoria->id }})"
                            class="btn btn-sm btn-outline-primary">Editar</button>
                        <button wire:click="cambiarEstado({{ $categoria->id }})"
                            wire:confirm="¿Seguro que quieres {{ $categoria->estado === 'ACTIVO' ? 'desactivar' : 'activar' }} esta categoría?"
                            class="btn btn-sm btn-outline-{{ $categoria->estado === 'ACTIVO' ? 'danger' : 'success' }}">
                            {{ $categoria->estado === 'ACTIVO' ? 'Desactivar' : 'Activar' }}
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted">No hay categorías aún.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>
