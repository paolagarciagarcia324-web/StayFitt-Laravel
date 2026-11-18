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
            'genero' => ['required', Rule::in(['FEMENINO'])],
            'objetivo_principal' => ['required', 'string', 'max:80'],
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
            'genero.in' => 'En modalidad presencial solo se admite género femenino.',
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
        ];
    }
}
