<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evidence extends Model
{
    // Laravel la buscaría en "evidence"; la tabla se llama "evidences".
    protected $table = 'evidences';

    // Campos que se guardan desde el formulario.
    protected $fillable = [
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
}
