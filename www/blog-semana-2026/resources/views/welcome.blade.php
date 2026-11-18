@extends('layouts.app')

@section('title', config('app.name'))

@section('content')
    <div class="row justify-content-center py-4">
        <div class="col-lg-8">
            <h1 class="display-5 fw-bold">Semana de Sistemas 2026</h1>
            <p class="lead">
                Blog del proyecto de Técnicas de Programación para Internet.
            </p>
            <p>
                Aquí se publicarán las experiencias de las ponencias, talleres y
                actividades de la semana.
            </p>
            <span class="badge text-bg-secondary">En construcción</span>

            @guest
                <div class="mt-4">
                    <a href="{{ route('login') }}" class="btn btn-primary">Ingresar al panel</a>
                </div>
            @endguest

            @auth
                <div class="mt-4">
                    <a href="{{ route('panel.index') }}" class="btn btn-primary">Ir al panel</a>
                </div>
            @endauth
        </div>
    </div>
@endsection
