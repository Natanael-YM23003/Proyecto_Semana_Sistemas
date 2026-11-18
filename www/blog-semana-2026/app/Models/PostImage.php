<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PostImage extends Model
{
    // Campos que se guardan desde el formulario.
    protected $fillable = [
        'post_id',
        'path',
        'caption',
        'sort_order',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // Ruta pública de la imagen.
    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
