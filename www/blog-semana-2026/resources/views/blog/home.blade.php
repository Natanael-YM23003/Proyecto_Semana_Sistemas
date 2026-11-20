@extends('layouts.public')

@section('title', 'Semana de Sistemas 2026')

@section('content')
    <section class="public-hero text-white py-5">
        <div class="container py-lg-4">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <p class="text-uppercase small hero-kicker mb-2">
                        Universidad de El Salvador &middot; Facultad Multidisciplinaria Oriental
                    </p>
                    <h1 class="display-5 fw-bold text-white">Semana de Sistemas 2026</h1>
                    <p class="lead hero-subtitle mb-4">
                        Ponencias, talleres, feria de proyectos y actividades de convivencia
                        contadas por sus autores.
                    </p>

                    <form method="GET" action="{{ route('blog.index') }}"
                          class="row g-2 justify-content-center" role="search">
                        <div class="col-12 col-md-8">
                            <label for="buscar" class="visually-hidden">Buscar entradas</label>
                            <input id="buscar" type="search" name="buscar"
                                   class="form-control form-control-lg border-0"
                                   placeholder="Buscar por título, tema o expositor">
                        </div>
                        <div class="col-12 col-md-auto">
                            <button type="submit" class="btn btn-sds-green btn-lg w-100 px-4">Buscar</button>
                        </div>
                    </form>

                    <p class="small hero-counter mt-3 mb-0">
                        {{ $total }} {{ $total === 1 ? 'entrada publicada' : 'entradas publicadas' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="container py-5">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
            <div>
                <h2 class="h3 fw-bold mb-1">Últimas entradas</h2>
                <p class="text-muted fw-semibold mb-0">Lo más reciente de la semana.</p>
            </div>
            @if ($latest->isNotEmpty())
                <a href="{{ route('blog.index') }}" class="btn btn-sds-category">Ver todas</a>
            @endif
        </div>

        @if ($latest->isEmpty())
            <div class="card card-sds-empty shadow-sm">
                <div class="card-body text-center py-5">
                    <h3 class="h5 fw-bold text-dark">Todavía no hay entradas publicadas</h3>
                    <p class="text-muted fw-semibold mb-0">
                        Cuando el equipo publique la primera experiencia, aparecerá aquí.
                    </p>
                </div>
            </div>
        @else
            <div class="row g-4">
                @foreach ($latest as $post)
                    <div class="col-md-6 col-lg-4">
                        @include('blog._card', ['post' => $post])
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    @if ($categories->isNotEmpty())
        <section class="bg-white border-top py-5">
            <div class="container">
                <h2 class="h3 fw-bold mb-1">Categorías</h2>
                <p class="text-muted fw-semibold">Filtra las entradas por tipo de actividad.</p>

                <div class="d-flex flex-wrap gap-2">
                    @foreach ($categories as $category)
                        <a href="{{ route('blog.index', ['categoria' => $category->slug]) }}"
                           class="btn btn-sds-category">
                            {{ $category->name }}
                            <span class="badge badge-sds-count ms-1">{{ $category->posts_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection