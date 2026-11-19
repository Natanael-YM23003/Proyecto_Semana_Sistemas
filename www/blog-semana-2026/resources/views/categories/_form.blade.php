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
    <div class="col-md-6">
        <label for="name" class="form-label">Nombre</label>
        <input id="name" type="text" name="name" maxlength="80"
               value="{{ old('name', $category->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">El enlace de la categoría se genera solo a partir del nombre.</div>
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Descripción</label>
        <textarea id="description" name="description" rows="3" maxlength="255"
                  class="form-control @error('description') is-invalid @enderror"
        >{{ old('description', $category->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Opcional. Una línea sobre qué actividades incluye.</div>
    </div>
</div>
