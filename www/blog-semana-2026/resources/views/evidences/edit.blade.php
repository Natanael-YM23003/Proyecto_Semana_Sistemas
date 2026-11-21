@extends('layouts.app')

@section('title', 'Editar evidencia | '.config('app.name'))

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Editar evidencia</h1>
        <p class="text-muted mb-0">Actualiza la información o fotografía de la evidencia.</p>
    </div>

    <form method="POST" action="{{ route('evidences.update', $evidence) }}" enctype="multipart/form-data"
          class="card border-0 shadow-sm">
        @csrf
        @method('PUT')

        <div class="card-body p-4">
            @include('evidences._form')
        </div>
    </form>
@endsection
