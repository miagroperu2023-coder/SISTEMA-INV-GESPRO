<div class="container py-4">
    <h2 class="h4 fw-bold mb-4">Importar productos desde Excel</h2>

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    @if ($paso === 'subir')
        <div class="card mb-3" style="max-width: 600px;">
            <div class="card-body">
                <p class="small text-muted">
                    Descarga la <a href="{{ asset('plantillas/plantilla_importar_productos.xlsx') }}">plantilla de
                        ejemplo</a>,
                    llénala y súbela aquí.
                </p>
                <input type="file" wire:model="archivo" class="form-control form-control-sm mb-2" accept=".xlsx,.xls">
                @error('archivo')
                    <div class="text-danger small mb-2">{{ $message }}</div>
                @enderror
                <button wire:click="analizar" wire:loading.attr="disabled" class="btn btn-sm btn-primary">
                    <span wire:loading.remove>Analizar archivo</span>
                    <span wire:loading>Analizando...</span>
                </button>
            </div>
        </div>
    @endif

    @if ($paso === 'previsualizar')
        @php
            $ok = collect($filasAnalizadas)->where('estado', 'ok')->count();
            $existentes = collect($filasAnalizadas)->where('estado', 'ya_existe')->count();
            $duplicados = collect($filasAnalizadas)->where('estado', 'duplicado_archivo')->count();
            $conError = collect($filasAnalizadas)->where('estado', 'error')->count();
        @endphp
        <div class="alert alert-info">
            <strong>{{ $ok }}</strong> se crearán ·
            <strong>{{ $existentes }}</strong> ya existen ·
            <strong>{{ $duplicados }}</strong> repetidas ·
            <strong>{{ $conError }}</strong> con error
        </div>
        <div class="table-responsive mb-3" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Fila</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Color</th>
                        <th>Talla</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($filasAnalizadas as $fila)
                        <tr
                            class="{{ match ($fila['estado']) {
                                'ok' => 'table-success',
                                'ya_existe' => 'table-secondary',
                                'duplicado_archivo' => 'table-warning',
                                'error' => 'table-danger',
                            } }}">
                            <td>{{ $fila['numero_fila'] }}</td>
                            <td>{{ $fila['producto'] }}</td>
                            <td>{{ $fila['categoria'] }}</td>
                            <td>{{ $fila['color'] }}</td>
                            <td>{{ $fila['talla'] }}</td>
                            <td class="small">{{ $fila['mensaje'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button wire:click="cancelar" class="btn btn-sm btn-outline-secondary">Cancelar</button>
        <button wire:click="confirmarImportacion" class="btn btn-sm btn-primary"
            @if ($ok === 0) disabled @endif>
            Confirmar e importar {{ $ok }} producto(s)
        </button>
    @endif

    @if ($paso === 'procesando')
        <div wire:poll.3s="refrescarEstado" class="alert alert-info d-flex align-items-center gap-2">
            <div class="spinner-border spinner-border-sm"></div>
            <span>Procesando tu importación, esto puede tardar unos minutos. Te avisaremos aquí cuando termine.</span>
        </div>
    @endif
</div>
