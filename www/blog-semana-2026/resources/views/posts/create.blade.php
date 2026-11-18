@extends('layouts.app')

@section('title', 'Nueva entrada | '.config('app.name'))

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Nueva entrada</h1>
        <p class="text-muted mb-0">Registra una actividad de la Semana de Sistemas.</p>
    </div>

    <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data"
          class="card border-0 shadow-sm">
        @csrf

        <div class="card-body p-4">
            @include('posts._form')
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2 py-3">
            <a href="{{ route('posts.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar entrada</button>
        </div>
    </form>
@endsection
