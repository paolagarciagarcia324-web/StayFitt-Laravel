<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminComunidadChatUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol('ADMIN') ?? false;
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', 'in:ACTIVO,CERRADO'],
        ];
    }
}
