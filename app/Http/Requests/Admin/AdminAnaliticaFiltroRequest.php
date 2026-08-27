<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminAnaliticaFiltroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol('ADMIN') ?? false;
    }

    public function rules(): array
    {
        return [
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'modalidad' => ['nullable', 'string', 'max:20'],
            'estado' => ['nullable', 'string', 'max:40'],
            'pasarela' => ['nullable', 'string', 'max:30'],
            'id_plan' => ['nullable', 'integer'],
        ];
    }
}
