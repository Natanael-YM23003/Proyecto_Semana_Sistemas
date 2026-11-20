@extends('layouts.app')

@section('title', 'Recuperar contraseña | '.config('app.name'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card card-sds-empty shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-2">Recuperar contraseña</h1>
                    <p class="text-muted small mb-4">
                        Ingresa tu correo registrado y te enviaremos un enlace para crear una nueva clave.
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

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">Correo institucional o registrado</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   required autofocus autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-sds-green w-100 py-2">
                            Enviar enlace de recuperación
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center text-muted small mt-3">
                <a href="{{ route('login') }}" class="fw-semibold text-success text-decoration-none">Volver a iniciar sesión</a>
            </p>
        </div>
    </div>
@endsection