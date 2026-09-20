<div class="container py-4">

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    <h2 class="h4 fw-bold mb-4">Configuración del negocio</h2>

    <div class="card" style="max-width: 600px;">
        <div class="card-body">

            <div class="mb-3">
                <label class="form-label">Nombre comercial</label>
                <input type="text" wire:model="nombre_comercial" class="form-control">
                @error('nombre_comercial')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">¿Tienes RUC?</label>
                <select wire:model.live="tipo_documento" class="form-select">
                    <option value="sin_ruc">No, vendo con mi nombre</option>
                    <option value="ruc">Sí, tengo RUC</option>
                </select>
            </div>

            @if ($tipo_documento === 'ruc')
                <div class="mb-3">
                    <label class="form-label">RUC</label>
                    <input type="text" wire:model="numero_documento" class="form-control">
                    @error('numero_documento')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Razón social</label>
                    <input type="text" wire:model="razon_social" class="form-control">
                    @error('razon_social')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">¿Bajo qué régimen tributario estás?</label>
                    <select wire:model="regimen_tributario" class="form-select">
                        <option value="nrus">Nuevo RUS (emito boleta, sin IGV desglosado)</option>
                        <option value="general">Régimen General/Especial (boleta y factura, con IGV)</option>
                    </select>
                    @error('regimen_tributario')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <p class="small text-muted">
                    Con <strong>Nuevo RUS</strong> solo podrás emitir Ticket y Boleta (sin desglose de IGV).<br>
                    Con <strong>Régimen General/Especial</strong> podrás emitir Ticket, Boleta y Factura (con IGV
                    desglosado).
                </p>
            @endif

            <button wire:click="guardar" class="btn btn-primary">Guardar cambios</button>
        </div>
    </div>

</div>
