<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:80'],
            'apellidos' => ['required', 'string', 'max:80'],
            'correo' => ['required', 'email', 'max:150', 'unique:usuarios,correo'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'documento_identidad' => ['required', 'string', 'max:30', 'unique:usuarios,documento_identidad'],
            'codigo_coach' => ['nullable', 'string', 'max:40', 'unique:coaches,codigo_coach'],
            'especialidad' => ['required', 'string', 'max:120'],
            'biografia' => ['nullable', 'string', 'max:2000'],
            'certificaciones' => ['nullable', 'string', 'max:500'],
            'estado_coach' => ['required', Rule::in(['ACTIVO', 'INACTIVO', 'VACACIONES'])],
        ];
    }
}
