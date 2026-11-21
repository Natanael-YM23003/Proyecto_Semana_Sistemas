@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-8">
        <div class="mb-3">
            <label for="title" class="form-label fw-semibold">
                Título de la evidencia <span class="text-danger">*</span>
            </label>
            <input id="title" type="text" name="title"
                   value="{{ old('title', $evidence->title ?? '') }}"
                   class="form-control @error('title') is-invalid @enderror"
                   placeholder="Ej. Foto grupal con ponente de Ciberseguridad"
                   required autofocus>
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="event_reference" class="form-label fw-semibold">
                Referencia del evento <span class="text-danger">*</span>
            </label>
            <input list="predefined_events_list" id="event_reference" name="event_reference"
                   value="{{ old('event_reference', $evidence->event_reference ?? '') }}"
                   class="form-control @error('event_reference') is-invalid @enderror"
                   placeholder="Selecciona o escribe el evento..."
                   required autocomplete="off">
            <datalist id="predefined_events_list">
                @foreach ($predefinedEvents as $item)
                    <option value="{{ $item }}">
                @endforeach
            </datalist>
            @error('event_reference')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            <div class="mt-2">
                <span class="text-muted small d-block mb-1">Eventos sugeridos (clic para seleccionar):</span>
                <div class="d-flex flex-wrap gap-1">
                    @foreach ($predefinedEvents as $item)
                        <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill"
                                onclick="document.getElementById('event_reference').value = '{{ $item }}';">
                            {{ $item }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label fw-semibold">Descripción o notas (opcional)</label>
            <textarea id="description" name="description" rows="3"
                      class="form-control @error('description') is-invalid @enderror"
                      placeholder="Contexto de la fotografía, integrantes presentes, momentos destacados...">{{ old('description', $evidence->description ?? '') }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="row g-2">
            <div class="col-md-6">
                <label for="taken_at" class="form-label fw-semibold">Fecha de la foto/actividad</label>
                <input id="taken_at" type="date" name="taken_at"
                       value="{{ old('taken_at', isset($evidence) && $evidence->taken_at ? $evidence->taken_at->format('Y-m-d') : date('Y-m-d')) }}"
                       class="form-control @error('taken_at') is-invalid @enderror">
                @error('taken_at')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="post_id" class="form-label fw-semibold">Vincular a entrada (opcional)</label>
                <select id="post_id" name="post_id" class="form-select @error('post_id') is-invalid @enderror">
                    <option value="">-- Sin entrada vinculada --</option>
                    @foreach ($posts as $post)
                        <option value="{{ $post->id }}"
                            {{ old('post_id', $evidence->post_id ?? '') == $post->id ? 'selected' : '' }}>
                            {{ $post->title }} ({{ $post->activity_date->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
                @error('post_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-sds-empty shadow-sm h-100">
            <div class="card-body p-3">
                <label for="image" class="form-label fw-semibold">
                    Fotografía / Imagen <span class="text-danger">*</span>
                </label>
                <input id="image" type="file" name="image"
                       class="form-control @error('image') is-invalid @enderror"
                       accept="image/png,image/jpeg,image/webp,image/jpg"
                       {{ isset($evidence) ? '' : 'required' }}
                       onchange="previewEvidenceImage(this)">
                @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text small mb-3">Formatos: JPG, PNG, WebP (máx. 5 MB).</div>

                <div class="text-center p-2 rounded bg-light border" style="min-height: 180px;">
                    <div id="image_placeholder" class="{{ isset($evidence) ? 'd-none' : '' }} text-muted py-4">
                        <i class="bi bi-image fs-1 d-block mb-2"></i>
                        <span class="small">Vista previa de la imagen</span>
                    </div>

                    <img id="image_preview"
                         src="{{ isset($evidence) ? $evidence->imageUrl() : '#' }}"
                         alt="Vista previa"
                         class="img-fluid rounded {{ isset($evidence) ? '' : 'd-none' }}"
                         style="max-height: 200px; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
    <a href="{{ route('evidences.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    <button type="submit" class="btn btn-sds-green px-4">
        {{ isset($evidence) ? 'Actualizar evidencia' : 'Guardar evidencia' }}
    </button>
</div>

<script>
    function previewEvidenceImage(input) {
        const preview = document.getElementById('image_preview');
        const placeholder = document.getElementById('image_placeholder');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                if (placeholder) placeholder.classList.add('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
