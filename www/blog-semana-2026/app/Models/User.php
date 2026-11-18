<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // Campos que se guardan desde el formulario.
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // No se muestran al convertir el usuario a JSON.
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Entradas creadas por este usuario.
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    // Entradas donde aparece como autor.
    public function authoredPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)->withTimestamps();
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }
}
