@extends('layouts.app')

@section('title', 'Nueva contraseña | '.config('app.name'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card card-sds-empty shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-2">Restablecer contraseña</h1>
                    <p class="text-muted small mb-4">
                        Escribe tu nueva contraseña para acceder al panel.
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

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Correo</label>
                            <input id="email" type="email" name="email" value="{{ old('email', $email) }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   required autocomplete="username">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Nueva contraseña</label>
                            <input id="password" type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required autofocus autocomplete="new-password">
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

                        <button type="submit" class="btn btn-sds-green w-100 py-2">
                            Guardar nueva contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection