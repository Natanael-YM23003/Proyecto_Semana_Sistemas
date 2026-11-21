<?php

namespace App\Models;

use Database\Factories\EvidenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Evidence extends Model
{
    /** @use HasFactory<EvidenceFactory> */
    use HasFactory;

    // Laravel la buscaría en "evidence"; la tabla se llama "evidences".
    protected $table = 'evidences';

    public const EVENT_REFERENCES = [
        'Ponencia 1',
        'Ponencia 2',
        'Ponencia 3',
        'Taller',
        'Comelona de pizza',
        'Torneo de fútbol',
        'Feria de logros',
        'Bingo',
        'Parrillada',
    ];

    // Campos que se guardan desde el formulario.
    protected $fillable = [
        'title',
        'event_reference',
        'post_id',
        'user_id',
        'path',
        'description',
        'taken_at',
    ];

    protected function casts(): array
    {
        return [
            'taken_at' => 'date',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // URL pública para mostrar la imagen de la evidencia.
    public function imageUrl(): string
    {
        if (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://')) {
            return $this->path;
        }

        return Storage::disk('public')->url($this->path);
    }
}
