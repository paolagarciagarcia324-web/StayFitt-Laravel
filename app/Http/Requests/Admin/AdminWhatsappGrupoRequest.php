<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminWhatsappGrupoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol('ADMIN') ?? false;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'tipo' => ['required', 'string', 'max:20'],
            'criterio' => ['nullable', 'string', 'max:80'],
            'link_invitacion' => ['required', 'string', 'max:300'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
        ];
    }
}
