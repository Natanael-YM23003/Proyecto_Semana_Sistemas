@php
    $editing = isset($post);
    $selectedAuthors = collect(old('authors', $editing ? $post->authors->pluck('id')->all() : [auth()->id()]))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <p class="fw-semibold mb-1">Revisa los datos:</p>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-12">
        <label for="title" class="form-label">Título</label>
        <input id="title" type="text" name="title" maxlength="150"
               value="{{ old('title', $post->title ?? '') }}"
               class="form-control @error('title') is-invalid @enderror" required>
        @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="category_id" class="form-label">Categoría</label>
        <select id="category_id" name="category_id"
                class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">Selecciona una categoría</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"
                        @selected((int) old('category_id', $post->category_id ?? 0) === $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="activity_date" class="form-label">Fecha de la actividad</label>
        <input id="activity_date" type="date" name="activity_date"
               value="{{ old('activity_date', $editing ? $post->activity_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
               class="form-control @error('activity_date') is-invalid @enderror" required>
        @error('activity_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="status" class="form-label">Estado</label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="draft" @selected(old('status', $post->status ?? 'draft') === 'draft')>Borrador</option>
            <option value="published" @selected(old('status', $post->status ?? '') === 'published')>Publicada</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Los borradores no se muestran en el área pública.</div>
    </div>

    <div class="col-12">
        <label for="excerpt" class="form-label">Resumen</label>
        <textarea id="excerpt" name="excerpt" rows="2" maxlength="300"
                  class="form-control @error('excerpt') is-invalid @enderror"
        >{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
        @error('excerpt')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Opcional. Es el texto corto que se muestra en el listado.</div>
    </div>

    <div class="col-md-6">
        <label for="speaker" class="form-label">Expositor o responsable</label>
        <input id="speaker" type="text" name="speaker" maxlength="150"
               value="{{ old('speaker', $post->speaker ?? '') }}"
               class="form-control @error('speaker') is-invalid @enderror">
        @error('speaker')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Opcional. Quién dio la ponencia o dirigió la actividad.</div>
    </div>

    <div class="col-12">
        <label for="content" class="form-label">Desarrollo de la actividad</label>
        <textarea id="content" name="content" rows="10"
                  class="form-control @error('content') is-invalid @enderror" required
        >{{ old('content', $post->content ?? '') }}</textarea>
        @error('content')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">
            Cuenta qué se hizo: las ideas centrales en las ponencias, las herramientas y el
            procedimiento en los talleres, la dinámica del bingo, los proyectos y tecnologías
            de la feria, o cómo se desarrollaron las actividades de convivencia.
        </div>
    </div>

    <div class="col-12">
        <label for="learnings" class="form-label">Aprendizajes y resultados</label>
        <textarea id="learnings" name="learnings" rows="5"
                  class="form-control @error('learnings') is-invalid @enderror" required
        >{{ old('learnings', $post->learnings ?? '') }}</textarea>
        @error('learnings')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Lo que aprendieron o los resultados que dejó la actividad.</div>
    </div>

    <div class="col-12">
        <label for="reflection" class="form-label">Reflexión personal</label>
        <textarea id="reflection" name="reflection" rows="5"
                  class="form-control @error('reflection') is-invalid @enderror" required
        >{{ old('reflection', $post->reflection ?? '') }}</textarea>
        @error('reflection')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Una reflexión por entrada, en nombre de los autores.</div>
    </div>

    <div class="col-md-6">
        <label for="authors" class="form-label">Autores</label>
        <select id="authors" name="authors[]" multiple size="5"
                class="form-select @error('authors') is-invalid @enderror" required>
            @foreach ($users as $user)
                <option value="{{ $user->id }}"
                        @selected(in_array($user->id, $selectedAuthors, true))>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
        @error('authors')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Mantén Ctrl (o Cmd en Mac) para seleccionar varios integrantes.</div>
    </div>

    <div class="col-md-6">
        <label for="cover_image" class="form-label">Imagen principal</label>
        <input id="cover_image" type="file" name="cover_image"
               class="form-control @error('cover_image') is-invalid @enderror"
               accept="image/jpeg,image/png,image/webp">
        @error('cover_image')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">JPG, PNG o WebP. Máximo 4 MB.</div>

        @if ($editing && $post->cover_image)
            <img src="{{ $post->coverUrl() }}" alt="Imagen principal actual"
                 class="img-thumbnail mt-2" style="max-height: 140px;">
            <div class="form-text">Si subes otra, reemplaza la actual.</div>
        @endif
    </div>

    <div class="col-12">
        <label for="images" class="form-label">Imágenes adicionales</label>
        <input id="images" type="file" name="images[]" multiple
               class="form-control @error('images') is-invalid @enderror"
               accept="image/jpeg,image/png,image/webp">
        @error('images')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Opcional. Hasta 6 imágenes de 4 MB cada una.</div>
    </div>

    @if ($editing && $post->images->isNotEmpty())
        <div class="col-12">
            <p class="form-label mb-2">Imágenes actuales</p>
            <div class="row g-2">
                @foreach ($post->images as $image)
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 h-100">
                            <img src="{{ $image->url() }}" alt="Imagen de la entrada"
                                 class="img-fluid rounded mb-2"
                                 style="width: 100%; height: 90px; object-fit: cover;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="remove_images[]" value="{{ $image->id }}"
                                       id="remove_image_{{ $image->id }}">
                                <label class="form-check-label small" for="remove_image_{{ $image->id }}">
                                    Eliminar
                                </label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
