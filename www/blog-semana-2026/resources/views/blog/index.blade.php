@extends('layouts.public')

@section('title', 'Entradas | Semana de Sistemas 2026')

@section('content')
    <section class="bg-white border-bottom py-4">
        <div class="container">
            <div class="row align-items-end g-3">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-3">
                        <h1 class="h2 mb-1">Entradas</h1>
                        <a href="{{ route('evidences.index') }}" class="btn btn-sm btn-outline-success">
                            Evidencias
                        </a>
                    </div>
                    <p class="text-muted mb-0">Experiencias de las actividades de la semana.</p>
                </div>
                <div class="col-lg-5">
                    <form method="GET" action="{{ route('blog.index') }}"
                          class="d-flex gap-2" role="search">
                        @if ($category)
                            <input type="hidden" name="categoria" value="{{ $category->slug }}">
                        @endif
                        <label for="buscar" class="visually-hidden">Buscar entradas</label>
                        <input id="buscar" type="search" name="buscar" value="{{ $search }}"
                               class="form-control" placeholder="Buscar entradas">
                        <button type="submit" class="btn btn-primary">Buscar</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <div class="container py-5">
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('blog.index', $search !== '' ? ['buscar' => $search] : []) }}"
               class="btn btn-sm {{ $category ? 'btn-outline-secondary' : 'btn-dark' }}">
                Todas
            </a>

            @foreach ($categories as $item)
                <a href="{{ route('blog.index', ['categoria' => $item->slug] + ($search !== '' ? ['buscar' => $search] : [])) }}"
                   class="btn btn-sm {{ $category?->id === $item->id ? 'btn-dark' : 'btn-outline-secondary' }}">
                    {{ $item->name }}
                    <span class="badge text-bg-light ms-1">{{ $item->posts_count }}</span>
                </a>
            @endforeach
        </div>

        @if ($search !== '' || $category)
            <p class="text-muted">
                @if ($search !== '')
                    Resultados para <strong>"{{ $search }}"</strong>
                @endif
                @if ($search !== '' && $category)
                    en
                @endif
                @if ($category)
                    <strong>{{ $category->name }}</strong>
                @endif
                &middot; <a href="{{ route('blog.index') }}">Limpiar filtros</a>
            </p>
        @endif

        @if ($posts->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <h2 class="h5">No se encontraron entradas</h2>
                    <p class="text-muted mb-3">
                        Prueba con otra palabra o revisa todas las publicaciones.
                    </p>
                    <a href="{{ route('blog.index') }}" class="btn btn-outline-secondary">
                        Ver todas las entradas
                    </a>
                </div>
            </div>
        @else
            <p class="text-muted small mb-3">
                {{ $posts->total() }} {{ $posts->total() === 1 ? 'entrada' : 'entradas' }}
            </p>

            <div class="row g-4">
                @foreach ($posts as $post)
                    <div class="col-md-6 col-lg-4">
                        @include('blog._card', ['post' => $post])
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
@endsection
