<div class="container py-4">

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold mb-0">Inventario</h2>
        <button class="btn btn-primary" wire:click="abrirForm">+ Crear producto</button>
    </div>

    @if ($mostrarForm)
        <div class="card mb-4">
            <div class="card-body">
                <h3 class="h6 fw-bold mb-3">Nuevo producto</h3>

                <div class="row g-2 mb-3">
                    <div class="col-md-8">
                        <label class="form-label small">Nombre</label>
                        <input type="text" wire:model="nombre" class="form-control form-control-sm"
                            placeholder="Ej: Zapatillas Nike Air">
                        @error('nombre')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Categoría</label>
                        <select wire:model="categoria_id" class="form-select form-select-sm">
                            <option value="">Selecciona</option>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                        @error('categoria_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label small mb-1">Descripción</label>
                    <textarea wire:model="descripcion" class="form-control form-control-sm" rows="2"></textarea>
                </div>

                <p class="small text-muted mb-1">Llena solo las tallas/colores que vas a manejar</p>

                @foreach ($todasLasTallas as $tipo => $tallas)
                    <p class="small fw-semibold text-uppercase text-muted mb-1 mt-2">{{ $tipo }}</p>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Talla</th>
                                <th>Color</th>
                                <th>Compra</th>
                                <th>Venta</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tallas as $size)
                                <tr>
                                    <td>{{ $size->valor }}</td>
                                    <td><input type="text" wire:model="variantes.{{ $size->id }}.color"
                                            class="form-control form-control-sm" placeholder="Color"></td>
                                    <td><input type="number" step="0.01"
                                            wire:model="variantes.{{ $size->id }}.precio_compra"
                                            class="form-control form-control-sm"></td>
                                    <td><input type="number" step="0.01"
                                            wire:model="variantes.{{ $size->id }}.precio_venta"
                                            class="form-control form-control-sm"></td>
                                    <td><input type="number" wire:model="variantes.{{ $size->id }}.stock"
                                            class="form-control form-control-sm"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach

                <button wire:click="guardar" class="btn btn-sm btn-primary">Guardar</button>
                <button wire:click="$set('mostrarForm', false)" class="btn btn-sm btn-link">Cancelar</button>
            </div>
        </div>
    @endif

    <div class="mb-3">
        <input type="text" wire:model.live.debounce.300ms="buscarProducto" class="form-control"
            placeholder="🔍 Buscar producto por nombre..." autofocus>
    </div>

    @if ($productos->isEmpty() && $buscarProducto)
        <p class="text-muted text-center py-3">No se encontró ningún producto con "{{ $buscarProducto }}".</p>
    @endif

    @foreach ($productos as $producto)
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>{{ $producto->nombre }}</strong>
                    <span class="badge bg-light text-dark border">{{ $producto->category->nombre }}</span>
                </div>

                @if ($producto->variants->isEmpty())
                    <p class="text-muted small mb-2">Sin variantes aún.</p>
                @else
                    <table class="table table-sm align-middle mb-2">
                        <thead>
                            <tr>
                                <th>Variante</th>
                                <th>Talla</th>
                                <th style="width:100px">Compra</th>
                                <th style="width:100px">Venta</th>
                                <th style="width:80px">Stock</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($producto->variants as $variant)
                                <tr wire:key="variant-{{ $variant->id }}">
                                    <td>
                                        <input type="text" value="{{ $variant->color }}"
                                            wire:change="actualizarCampo({{ $variant->id }}, 'color', $event.target.value)"
                                            class="form-control form-control-sm">
                                    </td>
                                    <td>
                                        <select wire:change="actualizarTalla({{ $variant->id }}, $event.target.value)"
                                            wire:key="talla-select-{{ $variant->id }}-{{ $variant->size_id }}"
                                            class="form-select form-select-sm">
                                            @foreach ($todasLasTallas as $tipo => $tallas)
                                                <optgroup label="{{ $tipo }}">
                                                    @foreach ($tallas as $size)
                                                        <option value="{{ $size->id }}"
                                                            {{ $size->id === $variant->size_id ? 'selected' : '' }}>
                                                            {{ $size->valor }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" value="{{ $variant->precio_compra }}"
                                            wire:change="actualizarCampo({{ $variant->id }}, 'precio_compra', $event.target.value)"
                                            class="form-control form-control-sm">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" value="{{ $variant->precio_venta }}"
                                            wire:change="actualizarCampo({{ $variant->id }}, 'precio_venta', $event.target.value)"
                                            class="form-control form-control-sm">
                                    </td>
                                    <td>
                                        <input type="number" value="{{ $variant->stock }}"
                                            wire:change="actualizarCampo({{ $variant->id }}, 'stock', $event.target.value)"
                                            class="form-control form-control-sm">
                                    </td>
                                    <td>
                                        <button wire:click="eliminarVariante({{ $variant->id }})"
                                            wire:confirm="¿Eliminar esta variante?"
                                            class="btn btn-sm btn-outline-danger">×</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <button class="btn btn-sm btn-primary" wire:click="abrirNuevaVariante({{ $producto->id }})">
                    + Agregar variante
                </button>

                @if ($nuevaVarianteProductoId === $producto->id)
                    <div class="row g-2 mt-2">
                        <div class="col-md-2">
                            <select wire:model="nuevaVariante.size_id" class="form-select form-select-sm" required>
                                <option value="">Talla</option>
                                @foreach ($todasLasTallas as $tipo => $tallas)
                                    <optgroup label="{{ $tipo }}">
                                        @foreach ($tallas as $size)
                                            <option value="{{ $size->id }}">{{ $size->valor }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('nuevaVariante.size_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <input type="text" wire:model="nuevaVariante.color" placeholder="Color"
                                class="form-control form-control-sm">
                            @error('nuevaVariante.color')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" wire:model="nuevaVariante.precio_compra"
                                placeholder="Compra" class="form-control form-control-sm">
                            @error('nuevaVariante.precio_compra')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" wire:model="nuevaVariante.precio_venta"
                                placeholder="Venta" class="form-control form-control-sm">
                            @error('nuevaVariante.precio_venta')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-1">
                            <input type="number" wire:model="nuevaVariante.stock" placeholder="Stock"
                                class="form-control form-control-sm">
                            @error('nuevaVariante.stock')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <button wire:click="guardarNuevaVariante({{ $producto->id }})"
                                class="btn btn-sm btn-primary">Agregar</button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endforeach

</div>
