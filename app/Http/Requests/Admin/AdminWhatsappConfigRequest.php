<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminWhatsappConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol('ADMIN') ?? false;
    }

    public function rules(): array
    {
        return [
            'numero_business' => ['required', 'string', 'max:30'],
            'link_grupo_principal' => ['nullable', 'string', 'max:300'],
            'mensaje_bienvenida' => ['nullable', 'string', 'max:2000'],
            'horario_atencion' => ['nullable', 'string', 'max:120'],
            'activa' => ['nullable', 'boolean'],
            'api_token' => ['nullable', 'string', 'max:500'],
            'respuestas_automaticas' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
