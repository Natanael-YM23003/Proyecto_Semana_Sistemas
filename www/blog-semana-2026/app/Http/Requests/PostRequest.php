<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'activity_date' => ['required', 'date'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'speaker' => ['nullable', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'learnings' => ['required', 'string'],
            'reflection' => ['required', 'string'],
            'status' => ['required', 'in:draft,published'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'authors' => ['required', 'array', 'min:1'],
            'authors.*' => ['integer', 'distinct', 'exists:users,id'],
            'images' => ['nullable', 'array', 'max:6'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'category_id' => 'categoría',
            'activity_date' => 'fecha de la actividad',
            'excerpt' => 'resumen',
            'speaker' => 'expositor o responsable',
            'content' => 'desarrollo de la actividad',
            'learnings' => 'aprendizajes y resultados',
            'reflection' => 'reflexión personal',
            'status' => 'estado',
            'cover_image' => 'imagen principal',
            'authors' => 'autores',
            'authors.*' => 'autor',
            'images' => 'imágenes',
            'images.*' => 'imagen',
        ];
    }

    public function messages(): array
    {
        return [
            'authors.required' => 'Selecciona al menos un autor.',
            'images.max' => 'Puedes subir como máximo 6 imágenes adicionales.',
        ];
    }
}
