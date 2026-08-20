<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUsuarioPresencialRequest;
use App\Models\HorarioPresencial;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\Rol;
use App\Models\SolicitudCompra;
use App\Models\User;
use App\Services\SolicitudAprobacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function __construct(private SolicitudAprobacionService $activacion) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $usuarios = User::query()
            ->with(['roles', 'cliente.planesCliente.plan'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('nombres', 'like', "%{$q}%")
                        ->orWhere('apellidos', 'like', "%{$q}%")
                        ->orWhereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%{$q}%"]);
                });
            })
            ->orderByDesc('creado_en')
            ->paginate(20)
            ->withQueryString();

        $horarios = HorarioPresencial::activos()->get();
        $plan = $this->planPresencial();
        $roles = Rol::query()->orderBy('id_rol')->get();

        return view('admin.usuarios.index', compact('usuarios', 'horarios', 'plan', 'q', 'roles'));
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

        $codigo = 'SF-A-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));

        DB::transaction(function () use ($request, $plan, $horario, $codigo) {
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

            $this->activacion->aprobar($solicitud->fresh(['plan.modulos', 'pago']), 'Alta presencial desde Admin.');
        });

        return redirect()
            ->route('admin.usuarios.index')
            ->with('success', 'Usuario presencial creado. Acceso inicial: correo + documento.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $usuario = User::query()->findOrFail($id);

        $data = $request->validate([
            'estado' => ['required', 'in:PENDIENTE,ACTIVO,INACTIVO,BLOQUEADO'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id_rol'],
        ]);

        $usuario->update([
            'estado' => $data['estado'],
            'estado_cuenta' => $data['estado'],
        ]);

        if ($request->has('roles')) {
            $usuario->roles()->sync($data['roles'] ?? []);
        }

        return back()->with('success', 'Usuario actualizado.');
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
