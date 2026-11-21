@extends('layouts.public')

@section('title', 'Evidencias | Semana de Sistemas 2026')
@section('description', 'Registro fotográfico y evidencias visuales de las actividades de la Semana de Sistemas 2026.')

@section('content')
    <section class="bg-white border-bottom py-4">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h1 class="h2 mb-1">Evidencias</h1>
                    <p class="text-muted mb-0">Galería fotográfica y registro visual de las actividades de la semana.</p>
                </div>
                <div>
                    @auth
                        <a href="{{ route('evidences.create') }}" class="btn btn-sds-green">
                            + Subir evidencia
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                            Ingresar para subir evidencia
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <div class="container py-5">
        @if (session('status'))
            <div class="alert alert-success fw-semibold mb-4" role="alert">
                {{ session('status') }}
            </div>
        @endif

        {{-- Filtros por evento --}}
        <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
            <span class="text-muted small fw-bold me-1">Filtrar por evento:</span>
            <a href="{{ route('evidences.index') }}"
               class="btn btn-sm {{ empty($selectedEvent) ? 'btn-dark' : 'btn-outline-secondary' }}">
                Todas ({{ $evidences->total() }})
            </a>

            @foreach ($predefinedEvents as $item)
                @php
                    $count = $eventCounts[$item] ?? 0;
                @endphp
                <a href="{{ route('evidences.index', ['evento' => $item]) }}"
                   class="btn btn-sm {{ $selectedEvent === $item ? 'btn-dark' : 'btn-outline-secondary' }}">
                    {{ $item }}
                    @if ($count > 0)
                        <span class="badge text-bg-light ms-1">{{ $count }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        @if ($selectedEvent)
            <div class="mb-4">
                <span class="text-muted">
                    Mostrando evidencias de: <strong>{{ $selectedEvent }}</strong>
                    &middot; <a href="{{ route('evidences.index') }}" class="text-decoration-none">Ver todas</a>
                </span>
            </div>
        @endif

        @if ($evidences->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <h2 class="h5 mb-2">No se encontraron evidencias</h2>
                    <p class="text-muted mb-4">
                        @if ($selectedEvent)
                            Aún no se han registrado fotos para "{{ $selectedEvent }}".
                        @else
                            Aún no se ha registrado ninguna evidencia fotográfica en el blog.
                        @endif
                    </p>
                    @auth
                        <a href="{{ route('evidences.create') }}" class="btn btn-sds-green">
                            Subir la primera evidencia
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                            Iniciar sesión para agregar evidencia
                        </a>
                    @endauth
                </div>
            </div>
        @else
            <div class="row g-4">
                @foreach ($evidences as $evidence)
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <div class="card h-100 card-sds-empty shadow-sm overflow-hidden">
                            <div class="position-relative" style="background-color: #080a09;">
                                <img src="{{ $evidence->imageUrl() }}"
                                     alt="{{ $evidence->title }}"
                                     class="card-img-top"
                                     style="height: 220px; object-fit: cover; cursor: pointer;"
                                     loading="lazy"
                                     data-bs-toggle="modal"
                                     data-bs-target="#modalEvidence{{ $evidence->id }}">
                                <span class="position-absolute top-0 end-0 m-2 badge bg-dark bg-opacity-75 text-light fw-semibold">
                                    {{ $evidence->event_reference }}
                                </span>
                            </div>

                            <div class="card-body p-3 d-flex flex-column">
                                <h3 class="card-title h6 fw-bold mb-1" title="{{ $evidence->title }}">
                                    {{ $evidence->title }}
                                </h3>

                                @if ($evidence->description)
                                    <p class="card-text text-muted small mb-2" style="font-size: 0.85rem;">
                                        {{ Str::limit($evidence->description, 100) }}
                                    </p>
                                @endif

                                <div class="mt-auto pt-2 border-top small text-muted">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>
                                            {{ $evidence->taken_at ? $evidence->taken_at->format('d/m/Y') : $evidence->created_at->format('d/m/Y') }}
                                        </span>
                                        @if ($evidence->user)
                                            <span class="text-truncate ms-2" style="max-width: 130px;" title="{{ $evidence->user->name }}">
                                                {{ $evidence->user->name }}
                                            </span>
                                        @endif
                                    </div>

                                    @if ($evidence->post)
                                        <div class="mt-1">
                                            <a href="{{ route('blog.show', $evidence->post) }}"
                                               class="text-success text-decoration-none small fw-semibold">
                                                &rarr; Entrada: {{ Str::limit($evidence->post->title, 25) }}
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                @auth
                                    <div class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                        <a href="{{ route('evidences.edit', $evidence) }}"
                                           class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.8rem;">
                                            Editar
                                        </a>

                                        <form method="POST" action="{{ route('evidences.destroy', $evidence) }}"
                                              onsubmit="return confirm('¿Seguro que deseas eliminar esta evidencia?');"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.8rem;">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                @endauth
                            </div>
                        </div>
                    </div>

                    {{-- Modal para visualizar imagen en tamaño completo --}}
                    <div class="modal fade" id="modalEvidence{{ $evidence->id }}" tabindex="-1"
                         aria-labelledby="modalLabel{{ $evidence->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content bg-dark text-white border-0">
                                <div class="modal-header border-secondary">
                                    <h5 class="modal-title h6" id="modalLabel{{ $evidence->id }}">
                                        {{ $evidence->title }}
                                        <span class="badge bg-success ms-2">{{ $evidence->event_reference }}</span>
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body text-center p-0">
                                    <img src="{{ $evidence->imageUrl() }}"
                                         alt="{{ $evidence->title }}"
                                         class="img-fluid"
                                         style="max-height: 75vh; object-fit: contain;">
                                </div>
                                @if ($evidence->description)
                                    <div class="modal-footer border-secondary justify-content-start text-light small">
                                        <p class="mb-0">{{ $evidence->description }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $evidences->links() }}
            </div>
        @endif
    </div>
@endsection
