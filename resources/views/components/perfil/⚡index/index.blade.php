<div class="container py-4">
    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    <h2 class="h4 fw-bold mb-4">Mi perfil</h2>

    <div class="card" style="max-width: 500px;">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label small">Nombre</label>
                <input type="text" wire:model="name" class="form-control">
                @error('name')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label small">Correo</label>
                <input type="email" wire:model="email" class="form-control">
                @error('email')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label small">Nueva contraseña (déjalo vacío si no quieres cambiarla)</label>
                <input type="text" wire:model="password" class="form-control">
                @error('password')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label small">Confirmar nueva contraseña</label>
                <input type="text" wire:model="password_confirmation" class="form-control">
            </div>
            <button wire:click="guardar" class="btn btn-primary">Guardar cambios</button>
        </div>
    </div>
</div>
