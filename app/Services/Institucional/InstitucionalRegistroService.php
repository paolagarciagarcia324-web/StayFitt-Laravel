<?php

namespace App\Services\Institucional;

use App\Models\Cliente;
use App\Models\EnlaceRegistroInstitucional;
use App\Models\Institucion;
use App\Models\InvitacionInstitucional;
use App\Models\Plan;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InstitucionalRegistroService
{
    public function resolverEnlace(string $token): ?EnlaceRegistroInstitucional
    {
        return EnlaceRegistroInstitucional::query()
            ->with(['institucion', 'plan'])
            ->where('token', $token)
            ->first();
    }

    public function resolverInvitacion(string $token): ?InvitacionInstitucional
    {
        return InvitacionInstitucional::query()
            ->with(['institucion', 'plan'])
            ->where('token', $token)
            ->first();
    }

    public function enlaceValido(EnlaceRegistroInstitucional $enlace): ?string
    {
        if ($enlace->estado !== 'ACTIVO') {
            return 'Este enlace ya no está activo.';
        }

        if ($enlace->fecha_expiracion->isPast()) {
            return 'Este enlace ha expirado.';
        }

        if ((int) $enlace->usos_actuales >= (int) $enlace->cupos_maximos) {
            return 'Este enlace alcanzó el cupo máximo de registros.';
        }

        $inst = $enlace->institucion;
        if (! $inst || $inst->estado !== 'ACTIVA') {
            return 'La institución no tiene convenio activo.';
        }

        if ($inst->cupos_totales > 0 && $inst->cupos_usados >= $inst->cupos_totales) {
            return 'La institución no tiene cupos disponibles.';
        }

        return null;
    }

    public function invitacionValida(InvitacionInstitucional $inv): ?string
    {
        $inv->marcarExpiradaSiCorresponde();

        if ($inv->estadoEfectivo() !== 'PENDIENTE') {
            return 'Esta invitación ya no está disponible.';
        }

        $inst = $inv->institucion;
        if (! $inst || $inst->estado !== 'ACTIVA') {
            return 'La institución no tiene convenio activo.';
        }

        if ($inst->cupos_totales > 0 && $inst->cupos_usados >= $inst->cupos_totales) {
            return 'La institución no tiene cupos disponibles.';
        }

        return null;
    }

    public function registrarDesdeEnlace(EnlaceRegistroInstitucional $enlace, array $data): User
    {
        $error = $this->enlaceValido($enlace);
        if ($error) {
            abort(422, $error);
        }

        return DB::transaction(function () use ($enlace, $data) {
            $user = $this->crearUsuarioInstitucional($data);
            $this->vincularCliente($user, $enlace->institucion, $enlace->plan, $data);

            $enlace->increment('usos_actuales');
            $enlace->institucion->increment('cupos_usados');

            return $user;
        });
    }

    public function registrarDesdeInvitacion(InvitacionInstitucional $inv, array $data): User
    {
        $error = $this->invitacionValida($inv);
        if ($error) {
            abort(422, $error);
        }

        return DB::transaction(function () use ($inv, $data) {
            $plan = $inv->plan ?? Plan::query()->find($inv->institucion->id_plan_contratado);
            $user = $this->crearUsuarioInstitucional($data);
            $cliente = $this->vincularCliente($user, $inv->institucion, $plan, $data);

            $inv->update([
                'estado' => 'ACEPTADA',
                'aceptada_en' => now(),
                'id_cliente_vinculado' => $cliente->id_cliente,
            ]);

            $inv->institucion->increment('cupos_usados');

            return $user;
        });
    }

    protected function crearUsuarioInstitucional(array $data): User
    {
        $email = strtolower(trim((string) $data['correo']));

        if (User::query()->where('correo', $email)->orWhere('email', $email)->exists()) {
            abort(422, 'Ya existe una cuenta con este correo.');
        }

        $user = new User([
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'correo' => $email,
            'email' => $email,
            'telefono' => $data['telefono'] ?? null,
            'documento_identidad' => $data['documento'] ?? null,
            'estado' => 'ACTIVO',
            'estado_cuenta' => 'ACTIVO',
            'acepto_terminos' => true,
            'fecha_aceptacion_terminos' => now(),
        ]);
        $user->assignPassword($data['password']);
        $user->save();

        $idRol = Rol::query()
            ->where('codigo', 'CLIENTE_INSTITUCIONAL')
            ->orWhere('codigo_rol', 'CLIENTE_INSTITUCIONAL')
            ->value('id_rol');

        if ($idRol) {
            DB::table('user_roles')->insert(['id_user' => $user->id_user, 'id_rol' => $idRol]);
        } else {
            $idRolCliente = Rol::query()->where('codigo', 'CLIENTE')->orWhere('codigo_rol', 'CLIENTE')->value('id_rol');
            if ($idRolCliente) {
                DB::table('user_roles')->insert(['id_user' => $user->id_user, 'id_rol' => $idRolCliente]);
            }
        }

        return $user;
    }

    protected function vincularCliente(User $user, Institucion $institucion, ?Plan $plan, array $data): Cliente
    {
        $cliente = Cliente::create([
            'id_user' => $user->id_user,
            'id_institucion' => $institucion->id_institucion,
            'tipo_cliente' => 'INSTITUCIONAL',
            'modalidad' => $plan?->modalidad ?? 'VIRTUAL',
            'documento' => $data['documento'] ?? null,
            'ciudad' => $data['ciudad'] ?? $institucion->ciudad,
            'objetivo_principal' => $data['objetivo'] ?? 'Bienestar institucional',
            'estado_cliente' => 'ACTIVO',
            'fecha_alta' => now(),
            'fecha_activacion' => now(),
        ]);

        if ($plan) {
            DB::table('planes_cliente')->insert([
                'id_cliente' => $cliente->id_cliente,
                'id_plan' => $plan->id_plan,
                'estado' => 'ACTIVO',
                'estado_plan_cliente' => 'ACTIVO',
                'fecha_inicio' => now()->toDateString(),
                'fecha_fin' => now()->addDays((int) ($plan->duracion_dias ?: 30))->toDateString(),
                'creado_en' => now(),
            ]);
        }

        return $cliente;
    }

    public function generarTokenInvitacion(): string
    {
        return Str::lower(Str::random(48));
    }
}
