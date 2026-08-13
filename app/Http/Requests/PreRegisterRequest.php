<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'documento_identidad' => ['required', 'string', 'max:30'],
            'correo' => ['required', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'modalidad' => ['required', Rule::in(['VIRTUAL', 'PRESENCIAL'])],
            'metodo_pago' => ['required', Rule::in(['TRANSFERENCIA', 'NEQUI', 'DAVIPLATA', 'EFECTIVO', 'PSE', 'OTRO'])],
            'referencia_pago' => ['required', 'string', 'max:120'],
            'comprobante' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'acepto_terminos' => ['accepted'],
            'observacion_usuario' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'acepto_terminos.accepted' => 'Debes aceptar los términos y condiciones.',
            'comprobante.required' => 'Debes adjuntar el comprobante de pago.',
        ];
    }
}
