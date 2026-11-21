@extends('layouts.app')

@section('title', 'Ingresar | '.config('app.name'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card card-sds-empty shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-2">Ingresar al panel</h1>
                    <p class="text-muted small mb-4">
                        Solo los integrantes del equipo pueden administrar el blog.
                    </p>

                    @if (session('status'))
                        <div class="alert alert-success small fw-semibold" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Correo</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   required autofocus autocomplete="username">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label fw-semibold mb-1">Contraseña</label>
                                <a href="{{ route('password.request') }}" class="small fw-semibold text-success text-decoration-none">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>
                            <input id="password" type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required autocomplete="current-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Mantener la sesión abierta</label>
                        </div>

                        <button type="submit" class="btn btn-sds-green w-100 py-2">Entrar</button>
                    </form>

                    <div class="text-center mt-3 pt-3 border-top">
                        <span class="text-muted small">¿No tienes una cuenta?</span>
                        <a href="{{ route('register') }}" class="small fw-semibold text-success text-decoration-none ms-1">
                            Regístrate
                        </a>
                    </div>
                </div>
            </div>

            <p class="text-center text-muted small mt-3">
                <a href="{{ route('home') }}" class="fw-semibold text-success text-decoration-none">Volver al inicio</a>
            </p>
        </div>
    </div>
@endsection