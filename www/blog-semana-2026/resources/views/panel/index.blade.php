@extends('layouts.app')

@section('title', 'Panel y CRUD de Contenido | '.config('app.name'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Panel de Control &middot; CRUD</h1>
            <p class="text-muted fw-semibold mb-0">
                Gestiona, visualiza, edita y elimina las entradas del blog y las evidencias fotográficas.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('posts.create') }}" class="btn btn-primary btn-sm px-3">
                <i class="bi bi-file-earmark-plus"></i> + Nueva entrada
            </a>
            <a href="{{ route('evidences.create') }}" class="btn btn-sds-green btn-sm px-3">
                <i class="bi bi-camera"></i> + Nueva evidencia
            </a>
        </div>
    </div>

    {{-- Tarjetas de métricas --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-lg">
            <div class="card h-100 card-sds-empty shadow-sm">
                <div class="card-body p-3">
                    <p class="text-muted text-uppercase small fw-bold mb-1">Entradas</p>
                    <p class="h3 fw-bold mb-0 text-dark">{{ $counts['posts'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="card h-100 card-sds-empty shadow-sm">
                <div class="card-body p-3">
                    <p class="text-muted text-uppercase small fw-bold mb-1">Publicadas</p>
                    <p class="h3 fw-bold mb-0 text-success">{{ $counts['published'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="card h-100 card-sds-empty shadow-sm">
                <div class="card-body p-3">
                    <p class="text-muted text-uppercase small fw-bold mb-1">Borradores</p>
                    <p class="h3 fw-bold mb-0 text-warning">{{ $counts['drafts'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="card h-100 card-sds-empty shadow-sm">
                <div class="card-body p-3">
                    <p class="text-muted text-uppercase small fw-bold mb-1">Evidencias</p>
                    <p class="h3 fw-bold mb-0 text-success">{{ $counts['evidences'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="card h-100 card-sds-empty shadow-sm">
                <div class="card-body p-3">
                    <p class="text-muted text-uppercase small fw-bold mb-1">Categorías</p>
                    <p class="h3 fw-bold mb-0 text-dark">{{ $counts['categories'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- CRUD Unificado: Entradas y Evidencias --}}
    <div class="card card-sds-empty shadow-sm">
        <div class="card-header bg-white border-bottom p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                {{-- Pestañas de filtrado --}}
                <ul class="nav nav-pills" id="crudTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold py-1 px-3" id="todos-tab"
                                data-bs-toggle="tab" data-bs-target="#tab-todos" type="button" role="tab"
                                aria-controls="tab-todos" aria-selected="true">
                            Todo el contenido ({{ $combined->count() }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold py-1 px-3" id="entradas-tab"
                                data-bs-toggle="tab" data-bs-target="#tab-entradas" type="button" role="tab"
                                aria-controls="tab-entradas" aria-selected="false">
                            Solo Entradas ({{ $posts->count() }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold py-1 px-3" id="evidencias-tab"
                                data-bs-toggle="tab" data-bs-target="#tab-evidencias" type="button" role="tab"
                                aria-controls="tab-evidencias" aria-selected="false">
                            Solo Evidencias ({{ $evidences->count() }})
                        </button>
                    </li>
                </ul>

                {{-- Buscador --}}
                <form method="GET" action="{{ route('panel.index') }}" class="d-flex gap-2">
                    <input type="search" name="q" value="{{ $busqueda }}"
                           class="form-control form-control-sm"
                           placeholder="Buscar por título o evento..."
                           style="min-width: 220px;">
                    <button type="submit" class="btn btn-sm btn-dark">Buscar</button>
                    @if ($busqueda !== '')
                        <a href="{{ route('panel.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="tab-content" id="crudTabsContent">

                {{-- TAB 1: TODO EL CONTENIDO COMBINADO --}}
                <div class="tab-pane fade show active" id="tab-todos" role="tabpanel" aria-labelledby="todos-tab">
                    @if ($combined->isEmpty())
                        <div class="text-center text-muted py-5">
                            <p class="mb-2">No se encontraron registros de entradas ni evidencias.</p>
                            @if ($busqueda)
                                <a href="{{ route('panel.index') }}" class="btn btn-sm btn-outline-secondary">Ver todo el contenido</a>
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 100px;">Tipo</th>
                                        <th scope="col" style="width: 70px;">Imagen</th>
                                        <th scope="col">Título</th>
                                        <th scope="col">Evento / Categoría</th>
                                        <th scope="col">Fecha</th>
                                        <th scope="col">Autor</th>
                                        <th scope="col" class="text-end" style="min-width: 170px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($combined as $item)
                                        <tr>
                                            <td>
                                                @if ($item->type === 'post')
                                                    <span class="badge bg-primary-subtle text-primary fw-semibold">
                                                        Entrada
                                                    </span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success fw-semibold">
                                                        Evidencia
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($item->image_url)
                                                    <img src="{{ $item->image_url }}" alt="{{ $item->title }}"
                                                         class="rounded border"
                                                         style="width: 44px; height: 44px; object-fit: cover; cursor: pointer;"
                                                         onclick="abrirImagen('{{ $item->image_url }}', '{{ addslashes($item->title) }}')">
                                                @else
                                                    <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted"
                                                         style="width: 44px; height: 44px; font-size: 0.75rem;">
                                                        N/A
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ $item->edit_url }}" class="fw-bold text-dark text-decoration-none">
                                                    {{ $item->title }}
                                                </a>
                                                @if ($item->type === 'post')
                                                    <span class="badge {{ $item->status === 'published' ? 'text-bg-success' : 'text-bg-secondary' }} ms-1" style="font-size: 0.7rem;">
                                                        {{ $item->status === 'published' ? 'Publicada' : 'Borrador' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-dark-subtle text-dark fw-semibold">
                                                    {{ $item->reference }}
                                                </span>
                                            </td>
                                            <td class="text-nowrap small text-muted">
                                                {{ $item->date ? $item->date->format('d/m/Y') : '-' }}
                                            </td>
                                            <td class="small text-muted text-truncate" style="max-width: 140px;">
                                                {{ $item->author }}
                                            </td>
                                            <td class="text-end text-nowrap">
                                                {{-- Ver --}}
                                                @if ($item->type === 'post')
                                                    <a href="{{ $item->view_url }}" target="_blank"
                                                       class="btn btn-sm btn-outline-info py-0 px-2" style="font-size: 0.8rem;">
                                                        Ver
                                                    </a>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-info py-0 px-2" style="font-size: 0.8rem;"
                                                            onclick="abrirImagen('{{ $item->image_url }}', '{{ addslashes($item->title) }}')">
                                                        Ver
                                                    </button>
                                                @endif

                                                {{-- Editar --}}
                                                <a href="{{ $item->edit_url }}"
                                                   class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.8rem;">
                                                    Editar
                                                </a>

                                                {{-- Eliminar --}}
                                                <form method="POST" action="{{ $item->delete_url }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm('¿Seguro que deseas eliminar {{ $item->type_label }}: \'{{ $item->title }}\'? Esta acción no se puede deshacer.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.8rem;">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- TAB 2: SOLO ENTRADAS --}}
                <div class="tab-pane fade" id="tab-entradas" role="tabpanel" aria-labelledby="entradas-tab">
                    @if ($posts->isEmpty())
                        <div class="text-center text-muted py-5">
                            <p class="mb-2">No hay entradas registradas.</p>
                            <a href="{{ route('posts.create') }}" class="btn btn-sm btn-primary">Crear primera entrada</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 70px;">Portada</th>
                                        <th scope="col">Título</th>
                                        <th scope="col">Categoría</th>
                                        <th scope="col">Fecha</th>
                                        <th scope="col">Autores</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col" class="text-end" style="min-width: 170px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($posts as $post)
                                        <tr>
                                            <td>
                                                @if ($post->coverUrl())
                                                    <img src="{{ $post->coverUrl() }}" alt="{{ $post->title }}"
                                                         class="rounded border" style="width: 44px; height: 44px; object-fit: cover;">
                                                @else
                                                    <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted small"
                                                         style="width: 44px; height: 44px; font-size: 0.7rem;">Sin foto</div>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('posts.edit', $post) }}" class="fw-bold text-dark text-decoration-none">
                                                    {{ $post->title }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary-subtle text-secondary fw-semibold">
                                                    {{ $post->category?->name ?? 'Sin categoría' }}
                                                </span>
                                            </td>
                                            <td class="small text-muted">{{ $post->activity_date->format('d/m/Y') }}</td>
                                            <td class="small text-muted text-truncate" style="max-width: 150px;">
                                                {{ $post->authors->isNotEmpty() ? $post->authors->pluck('name')->join(', ') : ($post->creator?->name ?? 'Equipo') }}
                                            </td>
                                            <td>
                                                @if ($post->status === 'published')
                                                    <span class="badge text-bg-success">Publicada</span>
                                                @else
                                                    <span class="badge text-bg-secondary">Borrador</span>
                                                @endif
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <a href="{{ route('blog.show', $post) }}" target="_blank"
                                                   class="btn btn-sm btn-outline-info py-0 px-2" style="font-size: 0.8rem;">
                                                    Ver
                                                </a>
                                                <a href="{{ route('posts.edit', $post) }}"
                                                   class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.8rem;">
                                                    Editar
                                                </a>
                                                <form method="POST" action="{{ route('posts.destroy', $post) }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm('¿Eliminar la entrada \'{{ $post->title }}\'? Esta acción no se puede deshacer.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.8rem;">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- TAB 3: SOLO EVIDENCIAS --}}
                <div class="tab-pane fade" id="tab-evidencias" role="tabpanel" aria-labelledby="evidencias-tab">
                    @if ($evidences->isEmpty())
                        <div class="text-center text-muted py-5">
                            <p class="mb-2">No hay evidencias registradas.</p>
                            <a href="{{ route('evidences.create') }}" class="btn btn-sm btn-sds-green">Subir primera evidencia</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 70px;">Foto</th>
                                        <th scope="col">Título de la evidencia</th>
                                        <th scope="col">Evento de referencia</th>
                                        <th scope="col">Entrada vinculada</th>
                                        <th scope="col">Fecha</th>
                                        <th scope="col">Subido por</th>
                                        <th scope="col" class="text-end" style="min-width: 170px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($evidences as $evidence)
                                        <tr>
                                            <td>
                                                <img src="{{ $evidence->imageUrl() }}" alt="{{ $evidence->title }}"
                                                     class="rounded border"
                                                     style="width: 44px; height: 44px; object-fit: cover; cursor: pointer;"
                                                     onclick="abrirImagen('{{ $evidence->imageUrl() }}', '{{ addslashes($evidence->title) }}')">
                                            </td>
                                            <td>
                                                <a href="{{ route('evidences.edit', $evidence) }}" class="fw-bold text-dark text-decoration-none">
                                                    {{ $evidence->title }}
                                                </a>
                                                @if ($evidence->description)
                                                    <small class="text-muted d-block text-truncate" style="max-width: 280px;">
                                                        {{ $evidence->description }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success fw-semibold">
                                                    {{ $evidence->event_reference }}
                                                </span>
                                            </td>
                                            <td class="small">
                                                @if ($evidence->post)
                                                    <a href="{{ route('blog.show', $evidence->post) }}" target="_blank"
                                                       class="text-decoration-none text-muted">
                                                        {{ Str::limit($evidence->post->title, 25) }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Ninguna</span>
                                                @endif
                                            </td>
                                            <td class="small text-muted">
                                                {{ $evidence->taken_at ? $evidence->taken_at->format('d/m/Y') : $evidence->created_at->format('d/m/Y') }}
                                            </td>
                                            <td class="small text-muted text-truncate" style="max-width: 140px;">
                                                {{ $evidence->user?->name ?? 'Equipo' }}
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-info py-0 px-2" style="font-size: 0.8rem;"
                                                        onclick="abrirImagen('{{ $evidence->imageUrl() }}', '{{ addslashes($evidence->title) }}')">
                                                    Ver
                                                </button>
                                                <a href="{{ route('evidences.edit', $evidence) }}"
                                                   class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.8rem;">
                                                    Editar
                                                </a>
                                                <form method="POST" action="{{ route('evidences.destroy', $evidence) }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm('¿Eliminar la evidencia \'{{ $evidence->title }}\'? Esta acción no se puede deshacer.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.8rem;">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Modal para visualizar imagen en tamaño completo --}}
    <div class="modal fade" id="modalImagenCrud" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-0">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title h6" id="modalImagenTitulo">Vista previa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img id="modalImagenSrc" src="" alt="Vista previa" class="img-fluid" style="max-height: 80vh; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>

    <script>
        function abrirImagen(url, titulo) {
            document.getElementById('modalImagenSrc').src = url;
            document.getElementById('modalImagenTitulo').textContent = titulo;
            const modal = new bootstrap.Modal(document.getElementById('modalImagenCrud'));
            modal.show();
        }
    </script>
@endsection
