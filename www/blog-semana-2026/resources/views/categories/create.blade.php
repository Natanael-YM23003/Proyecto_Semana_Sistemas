@extends('layouts.app')

@section('title', 'Nueva categoría | '.config('app.name'))

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Nueva categoría</h1>
        <p class="text-muted mb-0">Agrupa las entradas por tipo de actividad.</p>
    </div>

    <form method="POST" action="{{ route('categories.store') }}" class="card border-0 shadow-sm">
        @csrf

        <div class="card-body p-4">
            @include('categories._form')
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2 py-3">
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar categoría</button>
        </div>
    </form>
@endsection
