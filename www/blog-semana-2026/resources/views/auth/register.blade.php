@extends('layouts.app')

@section('title', 'Crear cuenta | '.config('app.name'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card card-sds-empty shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-2">Crear una cuenta</h1>
                    <p class="text-muted small mb-4">
                        Regístrate para colaborar y gestionar el blog de la Semana de Sistemas.
                    </p>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Nombre</label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}"
                                   class="form-control @error('name') is-invalid @enderror"
                                   required autofocus autocomplete="name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Correo institucional o personal</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   required autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Contraseña</label>
                            <input id="password" type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">Confirmar contraseña</label>
                            <input id="password_confirmation" type="password" name="password_confirmation"
                                   class="form-control"
                                   required autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn btn-sds-green w-100 py-2">Registrarse</button>
                    </form>

                    <div class="text-center mt-3 pt-3 border-top">
                        <span class="text-muted small">¿Ya tienes una cuenta?</span>
                        <a href="{{ route('login') }}" class="small fw-semibold text-success text-decoration-none ms-1">
                            Iniciar sesión
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
