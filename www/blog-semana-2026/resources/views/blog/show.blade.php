@extends('layouts.public')

@section('title', $post->title.' | Semana de Sistemas 2026')
@section('description', e($post->summary(160)))

@section('content')
    <div class="container py-4 py-lg-5">
        <nav aria-label="Ruta de navegación" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">Entradas</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $post->title }}</li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-lg-8">
                <article class="card border-0 shadow-sm">
                    @if ($post->coverUrl())
                        <img src="{{ $post->coverUrl() }}" class="card-img-top"
                             alt="{{ $post->title }}"
                             style="max-height: 400px; object-fit: cover;">
                    @endif

                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            @if ($post->category)
                                <a href="{{ route('blog.index', ['categoria' => $post->category->slug]) }}"
                                   class="badge bg-primary-subtle text-primary-emphasis text-decoration-none">
                                    {{ $post->category->name }}
                                </a>
                            @endif
                            <span class="text-muted small">
                                {{ $post->activity_date->format('d/m/Y') }}
                            </span>
                        </div>

                        <h1 class="h2 mb-3">{{ $post->title }}</h1>

                        @if ($post->speaker)
                            <p class="text-muted mb-1">
                                <span class="fw-semibold">Expositor:</span> {{ $post->speaker }}
                            </p>
                        @endif

                        @if ($post->authors->isNotEmpty())
                            <p class="text-muted mb-0">
                                <span class="fw-semibold">Por:</span>
                                {{ $post->authors->pluck('name')->join(', ') }}
                            </p>
                        @endif

                        @if ($post->excerpt)
                            <p class="lead mt-4">{{ $post->excerpt }}</p>
                        @endif

                        <hr class="my-4">

                        <h2 class="h5 mb-3">Desarrollo de la actividad</h2>
                        <div class="article-body">{!! nl2br(e($post->content)) !!}</div>

                        @if ($post->learnings)
                            <h2 class="h5 mt-5 mb-3">Aprendizajes y resultados</h2>
                            <div class="article-body">{!! nl2br(e($post->learnings)) !!}</div>
                        @endif

                        @if ($post->reflection)
                            <h2 class="h5 mt-5 mb-3">Reflexión personal</h2>
                            <div class="border-start border-4 border-primary ps-3 article-body">
                                {!! nl2br(e($post->reflection)) !!}
                            </div>
                        @endif
                    </div>
                </article>

                @if ($post->images->isNotEmpty())
                    <section class="card border-0 shadow-sm mt-4">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-3">Galería</h2>
                            <div class="row g-3">
                                @foreach ($post->images as $image)
                                    <div class="col-6 col-md-4">
                                        <a href="{{ $image->url() }}" target="_blank" rel="noopener">
                                            <img src="{{ $image->url() }}"
                                                 alt="Imagen de {{ $post->title }}"
                                                 class="img-fluid rounded"
                                                 style="width: 100%; height: 140px; object-fit: cover;"
                                                 loading="lazy">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endif

                @if ($related->isNotEmpty())
                    <section class="mt-5">
                        <h2 class="h4 mb-3">Más de {{ $post->category?->name ?? 'la semana' }}</h2>
                        <div class="row g-4">
                            @foreach ($related as $item)
                                <div class="col-md-4">
                                    @include('blog._card', ['post' => $item])
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h6 text-uppercase text-muted mb-3">Datos de la actividad</h2>

                        <dl class="mb-0">
                            <dt class="small text-muted">Fecha</dt>
                            <dd>{{ $post->activity_date->format('d/m/Y') }}</dd>

                            <dt class="small text-muted">Categoría</dt>
                            <dd>
                                @if ($post->category)
                                    <a href="{{ route('blog.index', ['categoria' => $post->category->slug]) }}"
                                       class="text-decoration-none">
                                        {{ $post->category->name }}
                                    </a>
                                @else
                                    Sin categoría
                                @endif
                            </dd>

                            @if ($post->speaker)
                                <dt class="small text-muted">Expositor</dt>
                                <dd>{{ $post->speaker }}</dd>
                            @endif

                            <dt class="small text-muted">Autores</dt>
                            <dd class="mb-0">
                                {{ $post->authors->isNotEmpty() ? $post->authors->pluck('name')->join(', ') : 'Equipo del blog' }}
                            </dd>
                        </dl>
                    </div>
                </div>

                <a href="{{ route('blog.index') }}" class="btn btn-outline-secondary w-100 mt-3">
                    Ver todas las entradas
                </a>
            </aside>
        </div>
    </div>
@endsection
