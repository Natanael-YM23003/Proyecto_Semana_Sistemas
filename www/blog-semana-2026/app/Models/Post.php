<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    // Campos que se guardan desde el formulario.
    protected $fillable = [
        'category_id',
        'user_id',
        'title',
        'slug',
        'activity_date',
        'excerpt',
        'speaker',
        'content',
        'learnings',
        'reflection',
        'cover_image',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Quién creó la entrada en el panel.
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Integrantes que firman la entrada.
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class)->orderBy('sort_order');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

    // Solo entradas publicadas.
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    // Solo borradores.
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    // Las rutas usan el slug.
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Ruta pública de la portada.
    public function coverUrl(): ?string
    {
        return $this->cover_image
            ? Storage::disk('public')->url($this->cover_image)
            : null;
    }
}
