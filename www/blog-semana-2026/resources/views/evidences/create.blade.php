@extends('layouts.app')

@section('title', 'Nueva evidencia | '.config('app.name'))

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Nueva evidencia</h1>
        <p class="text-muted mb-0">Agrega una imagen de evidencia para una actividad o evento de la semana.</p>
    </div>

    <form method="POST" action="{{ route('evidences.store') }}" enctype="multipart/form-data"
          class="card border-0 shadow-sm">
        @csrf

        <div class="card-body p-4">
            @include('evidences._form')
        </div>
    </form>
@endsection
