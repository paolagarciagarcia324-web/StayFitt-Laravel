<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUsuarioPresencialRequest extends FormRequest
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
            'tipo_documento' => ['required', Rule::in(['CC', 'CE', 'TI', 'PASAPORTE', 'OTRO'])],
            'documento_identidad' => ['required', 'string', 'max:30', 'unique:usuarios,documento_identidad'],
            'correo' => ['required', 'email', 'max:150', 'unique:usuarios,correo'],
            'telefono' => ['required', 'string', 'max:30'],
            'ciudad' => ['required', 'string', 'max:80'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'genero' => ['required', Rule::in(['FEMENINO', 'MASCULINO', 'OTRO', 'NO_INFORMA'])],
            'objetivo_principal' => ['required', 'string', 'max:80'],
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
            'id_horario' => [
                'required',
                'integer',
                Rule::exists('horarios_presenciales', 'id_horario')->where('activo', 1),
            ],
            'observacion_usuario' => ['nullable', 'string', 'max:1000'],
            'foto_perfil_url' => ['nullable', 'string', 'max:500'],
            'foto' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_horario.required' => 'Debes seleccionar el horario al que quieres pertenecer.',
            'id_horario.exists' => 'El horario seleccionado no está disponible.',
        ];
    }
}
