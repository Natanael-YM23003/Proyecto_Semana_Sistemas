<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'title' => ['required', 'string', 'max:255'],
            'event_reference' => ['required', 'string', 'max:255'],
            'image' => [
                $isCreate ? 'required' : 'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120',
            ],
            'post_id' => ['nullable', 'exists:posts,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'taken_at' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'event_reference' => 'referencia del evento',
            'image' => 'imagen',
            'post_id' => 'entrada relacionada',
            'description' => 'descripción',
            'taken_at' => 'fecha',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título de la evidencia es obligatorio.',
            'event_reference.required' => 'Debes indicar el evento de referencia.',
            'image.required' => 'Debes seleccionar una imagen para la evidencia.',
            'image.image' => 'El archivo debe ser una imagen válida.',
            'image.max' => 'La imagen no puede pesar más de 5 MB.',
        ];
    }
}
