<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'genero' => ['required', Rule::in(['FEMENINO', 'MASCULINO'])],
            'peso_kg' => ['required', 'numeric', 'gt:0', 'max:500'],
            'estatura_cm' => ['required', 'numeric', 'gt:0', 'max:300'],
            'cintura_cm' => ['required', 'numeric', 'gt:0', 'max:500'],
            'cadera_cm' => ['required', 'numeric', 'gt:0', 'max:500'],
            'brazo_cm' => ['required', 'numeric', 'gt:0', 'max:300'],
            'pierna_cm' => ['required', 'numeric', 'gt:0', 'max:300'],
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
            'nombres.required' => 'Ingresa tus nombres.',
            'apellidos.required' => 'Ingresa tus apellidos.',
            'tipo_documento.required' => 'Selecciona tu tipo de documento.',
            'documento_identidad.required' => 'Ingresa tu número de documento.',
            'correo.required' => 'Ingresa tu correo electrónico.',
            'correo.email' => 'Ingresa un correo electrónico válido.',
            'telefono.required' => 'Ingresa tu número de teléfono.',
            'ciudad.required' => 'Ingresa tu ciudad de residencia.',
            'genero.required' => 'Selecciona tu género.',
            'genero.in' => 'En modalidad virtual el género debe ser femenino o masculino.',
            'peso_kg.required' => 'La medida de peso es obligatoria.',
            'peso_kg.numeric' => 'El peso debe ser un valor numérico.',
            'peso_kg.gt' => 'El peso debe ser mayor a 0.',
            'estatura_cm.required' => 'La medida de estatura es obligatoria.',
            'estatura_cm.numeric' => 'La estatura debe ser un valor numérico.',
            'estatura_cm.gt' => 'La estatura debe ser mayor a 0.',
            'cintura_cm.required' => 'La medida de cintura es obligatoria.',
            'cintura_cm.numeric' => 'La medida de cintura debe ser un valor numérico.',
            'cintura_cm.gt' => 'La medida de cintura debe ser mayor a 0.',
            'cadera_cm.required' => 'La medida de cadera es obligatoria.',
            'cadera_cm.numeric' => 'La medida de cadera debe ser un valor numérico.',
            'cadera_cm.gt' => 'La medida de cadera debe ser mayor a 0.',
            'brazo_cm.required' => 'La medida de brazo es obligatoria.',
            'brazo_cm.numeric' => 'La medida de brazo debe ser un valor numérico.',
            'brazo_cm.gt' => 'La medida de brazo debe ser mayor a 0.',
            'pierna_cm.required' => 'La medida de pierna es obligatoria.',
            'pierna_cm.numeric' => 'La medida de pierna debe ser un valor numérico.',
            'pierna_cm.gt' => 'La medida de pierna debe ser mayor a 0.',
            'contacto_emergencia_nombre.required' => 'Ingresa el nombre del contacto de emergencia.',
            'contacto_emergencia_telefono.required' => 'Ingresa el teléfono del contacto de emergencia.',
            'acepto_terminos.accepted' => 'Debes aceptar los términos y condiciones de FigueFit.',
        ], $this->mensajesPagoSimulado());
    }
}
