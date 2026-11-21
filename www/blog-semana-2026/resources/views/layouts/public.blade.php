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
    <nav class="navbar navbar-expand-lg navbar-dark navbar-sds py-3">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                Semana de Sistemas 2026
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#menuPublico" aria-controls="menuPublico"
                    aria-expanded="false" aria-label="Mostrar menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuPublico">
                <ul class="navbar-nav me-auto ms-lg-3 gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('home')) active @endif"
                           href="{{ route('home') }}">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('blog.*')) active @endif"
                           href="{{ route('blog.index') }}">Entradas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if (request()->routeIs('evidences.*')) active @endif"
                           href="{{ route('evidences.index') }}">Evidencias</a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2 mt-2 mt-lg-0">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link btn-ingresar px-3" href="{{ route('panel.index') }}">Panel</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link @if (request()->routeIs('register')) active @endif"
                               href="{{ route('register') }}">Registrarse</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-ingresar px-3" href="{{ route('login') }}">Ingresar</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        @yield('content')
    </main>

    <footer class="footer-sds py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-1 footer-title">Semana de Sistemas 2026</p>
            <p class="mb-1 footer-subtitle small">
                Universidad de El Salvador &middot; Facultad Multidisciplinaria Oriental
            </p>
            <p class="mb-0 footer-accent small">
                Proyecto de Técnicas de Programación para Internet
            </p>
        </div>
    </footer>
</body>
</html>