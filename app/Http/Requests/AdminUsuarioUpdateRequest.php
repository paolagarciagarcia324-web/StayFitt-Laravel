<?php

namespace App\Http\Requests;

use App\Support\FiltersAssignableRoles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AdminUsuarioUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol('ADMIN') ?? false;
    }

    public function rules(): array
    {
        $id = (int) $this->route('id');

        return [
            'nombres' => ['required', 'string', 'max:80'],
            'apellidos' => ['required', 'string', 'max:80'],
            'tipo_documento' => ['required', Rule::in(['CC', 'CE', 'TI', 'PASAPORTE', 'OTRO'])],
            'documento_identidad' => [
                'required',
                'string',
                'max:30',
                Rule::unique('usuarios', 'documento_identidad')->ignore($id, 'id_user'),
            ],
            'correo' => [
                'required',
                'email',
                'max:150',
                Rule::unique('usuarios', 'correo')->ignore($id, 'id_user'),
            ],
            'telefono' => ['required', 'string', 'max:30'],
            'ciudad' => ['required', 'string', 'max:80'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'genero' => ['required', Rule::in(['FEMENINO', 'MASCULINO'])],
            'objetivo_principal' => ['required', 'string', 'max:80'],
            'estado' => ['required', 'in:PENDIENTE,ACTIVO,INACTIVO,BLOQUEADO'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['integer', 'exists:roles,id_rol'],
            'foto_perfil_url' => ['nullable', 'string', 'max:500'],
            'foto' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $adminId = FiltersAssignableRoles::adminRoleId();
            if ($adminId && in_array($adminId, $this->input('roles', []), true)) {
                $v->errors()->add('roles', 'No puedes asignar el rol ADMIN desde esta pantalla.');
            }
        });
    }

    /**
     * @return array<int>
     */
    public function sanitizedRoleIds(): array
    {
        return FiltersAssignableRoles::sanitize($this->input('roles', []));
    }
}
