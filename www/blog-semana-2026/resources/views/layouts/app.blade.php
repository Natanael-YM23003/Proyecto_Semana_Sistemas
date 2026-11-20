<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-sds py-3">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                Semana de Sistemas 2026
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#menuPrincipal" aria-controls="menuPrincipal"
                    aria-expanded="false" aria-label="Mostrar menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav me-auto ms-lg-3">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Inicio</a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link @if (request()->routeIs('panel.*')) active @endif"
                               href="{{ route('panel.index') }}">Panel</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if (request()->routeIs('posts.*')) active @endif"
                               href="{{ route('posts.index') }}">Entradas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if (request()->routeIs('categories.*')) active @endif"
                               href="{{ route('categories.index') }}">Categorías</a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-ingresar btn-sm my-2 my-lg-0 ms-lg-2">
                                    Cerrar sesión
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link btn-ingresar px-3" href="{{ route('login') }}">Ingresar</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4 flex-grow-1">
        @if (session('status'))
            <div class="alert alert-success fw-semibold" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger fw-semibold" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="footer-sds py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-1 footer-title">Semana de Sistemas 2026</p>
            <p class="mb-1 footer-subtitle small">
                Universidad de El Salvador &middot; Facultad Multidisciplinaria Oriental
            </p>
            <p class="mb-0 footer-accent small">
                Técnicas de Programación para Internet
            </p>
        </div>
    </footer>
</body>
</html>