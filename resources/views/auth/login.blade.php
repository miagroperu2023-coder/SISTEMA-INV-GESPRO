@extends('layouts.app')

@section('body')
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh;">
        <div class="card shadow-sm" style="max-width: 420px; width: 100%;">
            <div class="card-body p-4">

                <h2 class="h4 fw-bold text-center mb-4">Iniciar sesión</h2>

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror" placeholder="correo@ejemplo.com"
                            required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" name="password" id="password"
                            class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input">
                        <label for="remember" class="form-check-label">Recordarme</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                </form>

                <p class="text-center small mt-3">
                    ¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a>
                </p>

            </div>
        </div>
    </div>
@endsection
