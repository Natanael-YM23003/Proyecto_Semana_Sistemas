@extends('layouts.app')

@section('title', 'Entradas | '.config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Entradas</h1>
            <p class="text-muted mb-0">Publicaciones del blog.</p>
        </div>
        <a href="{{ route('posts.create') }}" class="btn btn-primary">Nueva entrada</a>
    </div>

    @if ($posts->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-muted">
                Todavía no hay entradas.
                <a href="{{ route('posts.create') }}">Crea la primera</a> para empezar el blog.
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Título</th>
                            <th scope="col">Categoría</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Autores</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posts as $post)
                            <tr>
                                <td>
                                    <a href="{{ route('posts.edit', $post) }}">{{ $post->title }}</a>
                                </td>
                                <td>{{ $post->category?->name ?? 'Sin categoría' }}</td>
                                <td class="text-nowrap">{{ $post->activity_date->format('d/m/Y') }}</td>
                                <td>{{ $post->authors->pluck('name')->join(', ') }}</td>
                                <td>
                                    @if ($post->status === 'published')
                                        <span class="badge text-bg-success">Publicada</span>
                                    @else
                                        <span class="badge text-bg-secondary">Borrador</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('posts.edit', $post) }}"
                                       class="btn btn-sm btn-outline-secondary">Editar</a>

                                    <form method="POST" action="{{ route('posts.destroy', $post) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('¿Eliminar esta entrada? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $posts->links() }}
        </div>
    @endif
@endsection
