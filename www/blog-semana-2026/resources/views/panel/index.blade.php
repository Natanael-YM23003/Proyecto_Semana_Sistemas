@extends('layouts.app')

@section('title', 'Panel | '.config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Panel</h1>
            <p class="text-muted mb-0">Hola, {{ auth()->user()->name }}.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted text-uppercase small mb-1">Entradas</p>
                    <p class="display-6 mb-0">{{ $counts['posts'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted text-uppercase small mb-1">Publicadas</p>
                    <p class="display-6 mb-0 text-success">{{ $counts['published'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted text-uppercase small mb-1">Borradores</p>
                    <p class="display-6 mb-0 text-warning">{{ $counts['drafts'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted text-uppercase small mb-1">Categorías</p>
                    <p class="display-6 mb-0">{{ $counts['categories'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <span class="fw-semibold">Últimas entradas</span>
        </div>

        @if ($latest->isEmpty())
            <div class="card-body text-muted">
                Todavía no hay entradas.
                <a href="{{ route('posts.create') }}">Crea la primera</a> para empezar el blog.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Título</th>
                            <th scope="col">Categoría</th>
                            <th scope="col">Fecha de la actividad</th>
                            <th scope="col">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($latest as $post)
                            <tr>
                                <td>
                                    <a href="{{ route('posts.edit', $post) }}">{{ $post->title }}</a>
                                </td>
                                <td>{{ $post->category?->name ?? 'Sin categoría' }}</td>
                                <td>{{ $post->activity_date->format('d/m/Y') }}</td>
                                <td>
                                    @if ($post->status === 'published')
                                        <span class="badge text-bg-success">Publicada</span>
                                    @else
                                        <span class="badge text-bg-secondary">Borrador</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
