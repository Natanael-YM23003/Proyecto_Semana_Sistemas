@extends('layouts.app')

@section('title', 'Editar entrada | '.config('app.name'))

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Editar entrada</h1>
        <p class="text-muted mb-0">{{ $post->title }}</p>
    </div>

    <form method="POST" action="{{ route('posts.update', $post) }}" enctype="multipart/form-data"
          class="card border-0 shadow-sm">
        @csrf
        @method('PUT')

        <div class="card-body p-4">
            @include('posts._form')
        </div>

        <div class="card-footer bg-white d-flex justify-content-end gap-2 py-3">
            <a href="{{ route('posts.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
    </form>
@endsection
