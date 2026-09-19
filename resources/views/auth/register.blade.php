@extends('layouts.app')

@section('body')
    <div class="container py-5">
        <div class="card shadow-sm mx-auto" style="max-width: 600px;">
            <div class="card-body p-4">

                <h2 class="h4 fw-bold mb-1">Crea tu cuenta y tu negocio</h2>
                <p class="text-muted mb-4">Regístrate para empezar a usar el sistema</p>

                <form method="POST" action="{{ route('register.store') }}">
                    @csrf

                    <h3 class="h6 fw-semibold mb-2">Tus datos</h3>

                    <div class="mb-2">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Correo</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>

                    <hr>

                    <h3 class="h6 fw-semibold mb-2">Tu negocio</h3>

                    <div class="mb-2">
                        <label class="form-label">Nombre comercial</label>
                        <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial') }}"
                            placeholder="Ej: Zapatos Usuario"
                            class="form-control @error('nombre_comercial') is-invalid @enderror">
                        @error('nombre_comercial')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre de tu primera tienda/sede</label>
                        <input type="text" name="nombre_sede" value="{{ old('nombre_sede') }}"
                            placeholder="Ej: Tienda central"
                            class="form-control @error('nombre_sede') is-invalid @enderror">
                        @error('nombre_sede')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- AQUÍ VA EXACTAMENTE EL BLOQUE QUE PREGUNTASTE --}}
                    <div class="mb-3">
                        <label class="form-label">¿Tienes RUC?</label>
                        <select name="tipo_documento" id="tipo_documento"
                            class="form-select @error('tipo_documento') is-invalid @enderror"
                            onchange="mostrarCamposRuc(this.value)">
                            <option value="sin_ruc" {{ old('tipo_documento') == 'sin_ruc' ? 'selected' : '' }}>No, vendo
                                con mi nombre</option>
                            <option value="ruc" {{ old('tipo_documento') == 'ruc' ? 'selected' : '' }}>Sí, tengo RUC
                            </option>
                        </select>
                        @error('tipo_documento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="campos-ruc" style="display: {{ old('tipo_documento') == 'ruc' ? 'block' : 'none' }};">
                        <div class="mb-2">
                            <label class="form-label">RUC</label>
                            <input type="text" name="numero_documento" value="{{ old('numero_documento') }}"
                                class="form-control @error('numero_documento') is-invalid @enderror">
                            @error('numero_documento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Razón social</label>
                            <input type="text" name="razon_social" value="{{ old('razon_social') }}"
                                class="form-control @error('razon_social') is-invalid @enderror">
                            @error('razon_social')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">¿Bajo qué régimen tributario estás?</label>
                            <select name="regimen_tributario"
                                class="form-select @error('regimen_tributario') is-invalid @enderror">
                                <option value="nrus" {{ old('regimen_tributario') == 'nrus' ? 'selected' : '' }}>Nuevo
                                    RUS (emito boleta, pago cuota fija, sin IGV desglosado)</option>
                                <option value="general" {{ old('regimen_tributario') == 'general' ? 'selected' : '' }}>
                                    Régimen General/Especial (emito boleta y factura, con IGV)</option>
                            </select>
                            @error('regimen_tributario')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- FIN DEL BLOQUE --}}

                    <button type="submit" class="btn btn-primary text-white w-100 py-2 mt-2">Crear mi cuenta</button>
                </form>

                <p class="text-center small mt-3">
                    ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
                </p>

            </div>
        </div>
    </div>

    <script>
        function mostrarCamposRuc(valor) {
            document.getElementById('campos-ruc').style.display = valor === 'ruc' ? 'block' : 'none';
        }
    </script>
@endsection
