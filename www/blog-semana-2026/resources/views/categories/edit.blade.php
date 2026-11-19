@extends('layouts.app')

@section('title', 'Editar categoría | '.config('app.name'))

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Editar categoría</h1>
        <p class="text-muted mb-0">{{ $category->name }}</p>
    </div>

    <form method="POST" action="{{ route('categories.update', $category) }}" class="card border-0 shadow-sm">
        @csrf
        @method('PUT')

        <div class="card-body p-4">
            @include('categories._form')
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2 py-3">
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
    </form>
@endsection
