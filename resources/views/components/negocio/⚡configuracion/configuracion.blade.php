<div class="container py-4">

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <h2 class="h4 fw-bold mb-4">Configuración del negocio</h2>

    <div class="card mb-3" style="max-width: 600px;">
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

    {{-- Solo negocios con RUC pueden conectar facturación electrónica --}}
    @if ($tipo_documento === 'ruc')
        <div class="card" style="max-width: 600px;">
            <div class="card-body">
                <h3 class="h6 fw-bold">Facturación electrónica (NubeFact)</h3>

                @if ($facturacion_electronica_activa)
                    <p class="text-success small">✓ Conectado y activo — las boletas/facturas se envían a SUNAT.</p>
                    <button wire:click="desactivarNubefact" class="btn btn-sm btn-outline-danger">Desactivar</button>
                @else
                    <p class="text-muted small">
                        Registra tu RUC en <a href="https://www.nubefact.com" target="_blank">nubefact.com</a>
                        y pega aquí el <strong>token</strong> de tu cuenta. El sistema nunca necesita tu Clave SOL.
                    </p>
                    <input type="text" wire:model="nubefact_token" class="form-control form-control-sm mb-2"
                        placeholder="Token de NubeFact">
                    @error('nubefact_token')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror
                    <button wire:click="guardarToken" class="btn btn-sm btn-primary">Guardar y activar</button>
                @endif
            </div>
        </div>
    @endif

</div>
