<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('description', 'Experiencias de las actividades de la Semana de Sistemas 2026.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                Semana de Sistemas 2026
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#menuPublico" aria-controls="menuPublico"
                    aria-expanded="false" aria-label="Mostrar menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuPublico">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('home')) active @endif"
                           href="{{ route('home') }}">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('blog.*')) active @endif"
                           href="{{ route('blog.index') }}">Entradas</a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('panel.index') }}">Panel</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Ingresar</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        @yield('content')
    </main>

    <footer class="border-top bg-white py-4 mt-auto">
        <div class="container">
            <div class="row g-2 align-items-center">
                <div class="col-md text-center text-md-start">
                    <p class="mb-0 fw-semibold">Semana de Sistemas 2026</p>
                    <p class="mb-0 text-muted small">
                        Universidad de El Salvador &middot; Facultad Multidisciplinaria Oriental
                    </p>
                </div>
                <div class="col-md text-center text-md-end text-muted small">
                    Proyecto de Técnicas de Programación para Internet
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
