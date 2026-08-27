<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminCuponUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol('ADMIN') ?? false;
    }

    public function rules(): array
    {
        return [
            'valor' => ['required', 'numeric', 'min:0'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
            'usos_maximos' => ['nullable', 'integer', 'min:1'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
        ];
    }
}
