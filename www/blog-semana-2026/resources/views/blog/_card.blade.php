<article class="card h-100 border-0 shadow-sm">
    @if ($post->coverUrl())
        <img src="{{ $post->coverUrl() }}" class="card-img-top post-cover"
             alt="{{ $post->title }}" loading="lazy">
    @else
        <div class="post-cover post-cover-placeholder d-flex align-items-center justify-content-center">
            <span class="text-uppercase small fw-semibold">
                {{ $post->category?->name ?? 'Entrada' }}
            </span>
        </div>
    @endif

    <div class="card-body d-flex flex-column">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            @if ($post->category)
                <a href="{{ route('blog.index', ['categoria' => $post->category->slug]) }}"
                   class="badge bg-primary-subtle text-primary-emphasis text-decoration-none">
                    {{ $post->category->name }}
                </a>
            @endif
            <span class="text-muted small">{{ $post->activity_date->format('d/m/Y') }}</span>
        </div>

        <h2 class="h5 card-title">
            <a href="{{ route('blog.show', $post) }}" class="text-decoration-none text-dark">
                {{ $post->title }}
            </a>
        </h2>

        @if ($post->authors->isNotEmpty())
            <p class="text-muted small mb-2">
                Por {{ $post->authors->pluck('name')->join(', ') }}
            </p>
        @endif

        <p class="card-text text-muted post-excerpt">{{ $post->summary() }}</p>

        <a href="{{ route('blog.show', $post) }}"
           class="mt-auto fw-semibold text-decoration-none">
            Leer entrada
        </a>
    </div>
</article>
