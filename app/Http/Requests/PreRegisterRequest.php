<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreRegisterRequest extends FormRequest
{
    use ValidaPagoSimulado;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepararPagoSimulado();
    }

    public function rules(): array
    {
        return array_merge([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'tipo_documento' => ['required', 'in:CC,CE,TI,PASAPORTE,OTRO'],
            'documento_identidad' => ['required', 'string', 'max:30'],
            'objetivo_principal' => ['nullable', 'string', 'max:80'],
            'correo' => ['required', 'email', 'max:150'],
            'telefono' => ['required', 'string', 'max:30'],
            'ciudad' => ['required', 'string', 'max:100'],
            'peso_kg' => ['required', 'numeric', 'min:30', 'max:300'],
            'estatura_cm' => ['required', 'numeric', 'min:100', 'max:250'],
            'cintura_cm' => ['required', 'numeric', 'min:40', 'max:200'],
            'cadera_cm' => ['required', 'numeric', 'min:40', 'max:200'],
            'brazo_cm' => ['required', 'numeric', 'min:15', 'max:80'],
            'pierna_cm' => ['required', 'numeric', 'min:20', 'max:120'],
            'alergias' => ['nullable', 'string', 'max:1000'],
            'condiciones_medicas' => ['nullable', 'string', 'max:1000'],
            'lesiones' => ['nullable', 'string', 'max:1000'],
            'medicamentos' => ['nullable', 'string', 'max:1000'],
            'contacto_emergencia_nombre' => ['required', 'string', 'max:120'],
            'contacto_emergencia_telefono' => ['required', 'string', 'max:30'],
            'acepto_terminos' => ['accepted'],
            'observacion_usuario' => ['nullable', 'string', 'max:1000'],
        ], $this->reglasPagoSimulado());
    }

    public function messages(): array
    {
        return array_merge([
            'acepto_terminos.accepted' => 'Debes aceptar los términos y condiciones.',
        ], $this->mensajesPagoSimulado());
    }
}
