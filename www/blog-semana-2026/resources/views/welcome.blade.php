<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Blog Semana de Sistemas 2026') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Blog Semana de Sistemas 2026') }}
            </a>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row justify-content-center">
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
            </div>
        </div>
    </main>
</body>
</html>
