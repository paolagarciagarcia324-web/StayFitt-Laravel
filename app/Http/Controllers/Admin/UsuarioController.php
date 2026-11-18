<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUsuarioPresencialRequest;
use App\Http\Requests\AdminUsuarioUpdateRequest;
use App\Models\AccesoClienteModulo;
use App\Models\Cliente;
use App\Models\HorarioPresencial;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\Rol;
use App\Models\SolicitudCompra;
use App\Models\User;
use App\Services\SolicitudAprobacionService;
use App\Support\FiltersAssignableRoles;
use App\Support\GeneratesTemporaryPassword;
use App\Support\ResolvesAdminMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    use ResolvesAdminMedia;

    public function __construct(private SolicitudAprobacionService $activacion) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $usuarios = User::query()
            ->with(['roles', 'cliente.planesCliente.plan'])
            ->whereHas('cliente')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('codigo', 'ADMIN')->orWhere('codigo_rol', 'ADMIN');
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('nombres', 'like', "%{$q}%")
                        ->orWhere('apellidos', 'like', "%{$q}%")
                        ->orWhere('documento_identidad', 'like', "%{$q}%")
                        ->orWhereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%{$q}%"]);
                });
            })
            ->orderByDesc('creado_en')
            ->paginate(10)
            ->withQueryString();

        $roles = FiltersAssignableRoles::forForms();
        $kpis = $this->buildPersonasKpis();

        return view('admin.usuarios.index', compact('usuarios', 'q', 'roles', 'kpis'));
    }

    public function store(AdminUsuarioPresencialRequest $request): RedirectResponse
    {
        $plan = $this->planPresencial();
        if (! $plan) {
            return back()->withErrors(['id_horario' => 'No hay plan presencial activo.'])->withInput();
        }

        $horario = HorarioPresencial::activos()
            ->where('id_horario', $request->integer('id_horario'))
            ->firstOrFail();

        if (! $horario->tieneCupo()) {
            return back()->withErrors(['id_horario' => 'Ese horario ya no tiene cupo.'])->withInput();
        }

        $codigo = SolicitudCompra::generarCodigo('PRESENCIAL', $request->nombres, $request->apellidos);

        $tempPassword = null;

        DB::transaction(function () use ($request, $plan, $horario, $codigo, &$tempPassword) {
            $datosExtra = [
                'tipo' => 'REGISTRO_PRESENCIAL',
                'origen_alta' => 'ADMIN',
                'sede' => 'Campoalegre, Huila',
                'tipo_documento' => $request->tipo_documento,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
                'objetivo_principal' => $request->objetivo_principal,
                'id_horario' => $horario->id_horario,
                'horario' => [
                    'id_horario' => $horario->id_horario,
                    'codigo' => $horario->codigo,
                    'nombre' => $horario->nombre,
                    'dias' => $horario->dias,
                    'hora_inicio' => substr((string) $horario->hora_inicio, 0, 5),
                    'hora_fin' => substr((string) $horario->hora_fin, 0, 5),
                    'link_whatsapp' => $horario->link_whatsapp,
                ],
                'medidas' => [
                    'peso_kg' => (float) $request->peso_kg,
                    'estatura_cm' => (float) $request->estatura_cm,
                    'cintura_cm' => (float) $request->cintura_cm,
                    'cadera_cm' => (float) $request->cadera_cm,
                    'brazo_cm' => (float) $request->brazo_cm,
                    'pierna_cm' => (float) $request->pierna_cm,
                ],
                'salud' => [
                    'alergias' => $request->alergias,
                    'condiciones_medicas' => $request->condiciones_medicas,
                    'lesiones' => $request->lesiones,
                    'medicamentos' => $request->medicamentos,
                    'contacto_emergencia_nombre' => $request->contacto_emergencia_nombre,
                    'contacto_emergencia_telefono' => $request->contacto_emergencia_telefono,
                ],
            ];

            $solicitud = SolicitudCompra::create([
                'codigo_solicitud' => $codigo,
                'id_plan' => $plan->id_plan,
                'origen' => 'ADMIN',
                'modalidad' => 'PRESENCIAL',
                'tipo_cliente' => 'INDIVIDUAL',
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'tipo_documento' => $request->tipo_documento,
                'documento' => $request->documento_identidad,
                'documento_identidad' => $request->documento_identidad,
                'correo' => $request->correo,
                'telefono' => $request->telefono,
                'ciudad' => $request->ciudad,
                'precio_lista' => $plan->precio,
                'descuento' => 0,
                'total' => $plan->precio,
                'acepto_terminos' => true,
                'fecha_aceptacion_terminos' => now(),
                'observacion_usuario' => $request->observacion_usuario,
                'datos_extra_json' => $datosExtra,
                'estado_solicitud' => 'PENDIENTE',
                'reserva_cupo' => true,
            ]);

            Pago::create([
                'id_solicitud' => $solicitud->id_solicitud,
                'id_plan' => $plan->id_plan,
                'origen_pago' => 'ADMIN',
                'monto' => $plan->precio,
                'moneda' => $plan->moneda,
                'tipo_pago' => 'UNICO',
                'metodo' => 'CAJA',
                'metodo_pago' => 'CAJA',
                'pasarela' => 'ADMIN',
                'referencia' => 'REF-'.$codigo,
                'referencia_pago' => 'REF-'.$codigo,
                'fecha_pago' => now(),
                'estado_pago' => 'PENDIENTE',
            ]);

            $tempPassword = $this->activacion->aprobar(
                $solicitud->fresh(['plan.modulos', 'pago']),
                'Alta presencial desde Admin.'
            );

            $foto = $this->resolveMedia($request, 'foto', 'avatars', 'foto_perfil_url');
            if ($foto) {
                User::query()->where('correo', $request->correo)->update(['foto_perfil_url' => $foto]);
            }
        });

        return redirect()
            ->route('admin.clientes.presenciales')
            ->with('cliente_ok', ['accion' => 'creado', 'temp_password' => $tempPassword]);
    }

    public function update(AdminUsuarioUpdateRequest $request, int $id): RedirectResponse
    {
        $usuario = User::query()->with('cliente')->findOrFail($id);
        $data = $request->validated();

        $usuario->update([
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'Tipo_documento' => $data['tipo_documento'],
            'documento_identidad' => $data['documento_identidad'],
            'correo' => $data['correo'],
            'email' => $data['correo'],
            'telefono' => $data['telefono'],
            'estado' => $data['estado'],
            'estado_cuenta' => $data['estado'],
            'foto_perfil_url' => $this->resolveMedia($request, 'foto', 'avatars', 'foto_perfil_url', $usuario->foto_perfil_url),
        ]);

        if ($usuario->cliente) {
            $usuario->cliente->update([
                'tipo_documento' => $data['tipo_documento'],
                'documento' => $data['documento_identidad'],
                'genero' => $data['genero'],
                'ciudad' => $data['ciudad'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'objetivo_principal' => $data['objetivo_principal'],
            ]);
        }

        $usuario->roles()->sync($request->sanitizedRoleIds());
        $usuario->load('roles', 'cliente');

        $this->revokeClienteIfNeeded($usuario);

        return back()->with('usuario_ok', ['accion' => 'actualizado']);
    }

    public function resetPassword(int $id): RedirectResponse
    {
        $usuario = User::query()->findOrFail($id);

        if ($usuario->tieneRol('ADMIN')) {
            abort(403, 'No puedes restablecer la contraseña de un administrador desde aquí.');
        }

        $temp = GeneratesTemporaryPassword::make();
        $usuario->assignPassword($temp);
        $usuario->save();

        return back()->with('usuario_ok', [
            'accion' => 'password_reset',
            'temp_password' => $temp,
            'correo' => $usuario->correo,
        ]);
    }

    private function buildPersonasKpis(): array
    {
        $sinAdmin = function ($query) {
            $query->where('codigo', 'ADMIN')->orWhere('codigo_rol', 'ADMIN');
        };

        $conRolCliente = function ($query) {
            $query->whereIn('codigo', ['CLIENTE', 'CLIENTE_INSTITUCIONAL'])
                ->orWhereIn('codigo_rol', ['CLIENTE', 'CLIENTE_INSTITUCIONAL']);
        };

        $baseClientes = User::query()
            ->whereHas('cliente')
            ->whereDoesntHave('roles', $sinAdmin);

        $total = (clone $baseClientes)->count();
        $activos = (clone $baseClientes)->where(function ($q) {
            $q->whereIn('estado', ['ACTIVO', 'ACTIVA'])->orWhereIn('estado_cuenta', ['ACTIVO', 'ACTIVA']);
        })->count();
        $inactivos = (clone $baseClientes)->where(function ($q) {
            $q->whereIn('estado', ['INACTIVO', 'SUSPENDIDO', 'BLOQUEADO'])
                ->orWhereIn('estado_cuenta', ['INACTIVO', 'SUSPENDIDO', 'BLOQUEADO']);
        })->count();
        $admins = User::query()
            ->whereHas('roles', fn ($q) => $q->where('codigo', 'ADMIN')->orWhere('codigo_rol', 'ADMIN'))
            ->count();

        $mes = now()->startOfMonth();
        $delta = function (int $nowCount, $query) use ($mes): int {
            $antes = (clone $query)->where('creado_en', '<', $mes)->count();
            if ($antes < 1) {
                return $nowCount > 0 ? 100 : 0;
            }

            return (int) round((($nowCount - $antes) / max(1, $antes)) * 100);
        };

        return [
            'total' => $total,
            'activos' => $activos,
            'inactivos' => $inactivos,
            'admins' => $admins,
            'delta_total' => $delta($total, $baseClientes),
            'delta_activos' => $delta($activos, (clone $baseClientes)->where(function ($q) {
                $q->whereIn('estado', ['ACTIVO', 'ACTIVA'])->orWhereIn('estado_cuenta', ['ACTIVO', 'ACTIVA']);
            })),
            'delta_inactivos' => $delta($inactivos, (clone $baseClientes)->where(function ($q) {
                $q->whereIn('estado', ['INACTIVO', 'SUSPENDIDO', 'BLOQUEADO'])
                    ->orWhereIn('estado_cuenta', ['INACTIVO', 'SUSPENDIDO', 'BLOQUEADO']);
            })),
            'delta_admins' => $delta($admins, User::query()->whereHas('roles', fn ($q) => $q->where('codigo', 'ADMIN')->orWhere('codigo_rol', 'ADMIN'))),
            'virtuales' => Cliente::query()
                ->where('modalidad', 'VIRTUAL')
                ->whereHas('usuario.roles', $conRolCliente)
                ->count(),
            'presenciales' => Cliente::query()
                ->where('modalidad', 'PRESENCIAL')
                ->whereHas('usuario.roles', $conRolCliente)
                ->count(),
            'coaches' => User::query()
                ->whereHas('roles', fn ($q) => $q->where('codigo', 'COACH')->orWhere('codigo_rol', 'COACH'))
                ->count(),
            'asistentes' => User::query()
                ->whereHas('roles', fn ($q) => $q->where('codigo', 'ASISTENTE_TECNICO')->orWhere('codigo_rol', 'ASISTENTE_TECNICO'))
                ->count(),
        ];
    }

    private function revokeClienteIfNeeded(User $usuario): void
    {
        $esCliente = $usuario->roles->contains(function ($rol) {
            $codigo = strtoupper((string) ($rol->codigo ?: $rol->codigo_rol));

            return in_array($codigo, ['CLIENTE', 'CLIENTE_INSTITUCIONAL'], true);
        });

        if ($esCliente || ! $usuario->cliente) {
            return;
        }

        $cliente = $usuario->cliente;
        $cliente->update(['estado_cliente' => 'INACTIVO']);

        AccesoClienteModulo::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->update(['estado_acceso' => 'SUSPENDIDO']);
    }

    private function planPresencial(): ?Plan
    {
        return Plan::activos()
            ->where('modalidad', 'PRESENCIAL')
            ->where(function ($query) {
                $query->where('codigo', 'PRESENCIAL-PLUS')
                    ->orWhere('slug', 'plan-presencial-sala');
            })
            ->first()
            ?? Plan::activos()->where('modalidad', 'PRESENCIAL')->first();
    }
}
