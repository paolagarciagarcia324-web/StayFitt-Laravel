<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminCuponStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol('ADMIN') ?? false;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:40', 'unique:descuentos_cupones,codigo'],
            'tipo' => ['required', 'in:PORCENTAJE,MONTO'],
            'valor' => ['required', 'numeric', 'min:0'],
            'id_plan' => ['nullable', 'integer', 'exists:planes,id_plan'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
            'usos_maximos' => ['nullable', 'integer', 'min:1'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
        ];
    }
}
