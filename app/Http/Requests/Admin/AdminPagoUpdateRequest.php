<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminPagoUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol('ADMIN') ?? false;
    }

    public function rules(): array
    {
        return [
            'estado_pago' => ['required', 'in:PENDIENTE,VALIDADO,RECHAZADO'],
            'observacion_admin' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
