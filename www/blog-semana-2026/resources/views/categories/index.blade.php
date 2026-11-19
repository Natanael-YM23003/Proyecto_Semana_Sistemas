@extends('layouts.app')

@section('title', 'Categorías | '.config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Categorías</h1>
            <p class="text-muted mb-0">Tipos de actividad que se usan en las entradas.</p>
        </div>
        <a href="{{ route('categories.create') }}" class="btn btn-primary">Nueva categoría</a>
    </div>

    @if ($categories->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-muted">
                Todavía no hay categorías.
                <a href="{{ route('categories.create') }}">Crea la primera</a> para organizar el blog.
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Entradas</th>
                            <th scope="col" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td>
                                    <a href="{{ route('categories.edit', $category) }}">{{ $category->name }}</a>
                                </td>
                                <td class="text-muted">{{ $category->description ?: 'Sin descripción' }}</td>
                                <td>{{ $category->posts_count }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('categories.edit', $category) }}"
                                       class="btn btn-sm btn-outline-secondary">Editar</a>

                                    <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('¿Eliminar esta categoría? Esta acción no se puede deshacer.');">
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
    @endif
@endsection
