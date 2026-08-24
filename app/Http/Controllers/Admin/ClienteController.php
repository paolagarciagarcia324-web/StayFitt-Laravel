<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccesoClienteModulo;
use App\Models\Cliente;
use App\Models\Coach;
use App\Models\HistorialAsignacionCoach;
use App\Models\HorarioPresencial;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.clientes.virtuales');
    }

    public function virtuales(Request $request): View
    {
        return $this->listar($request, 'VIRTUAL', 'virtuales');
    }

    public function presenciales(Request $request): View
    {
        return $this->listar($request, 'PRESENCIAL', 'presenciales');
    }

    public function show(int $id): View
    {
        $cliente = $this->loadCliente($id);

        return view('admin.clientes.show', compact('cliente'));
    }

    public function edit(int $id): View
    {
        $cliente = $this->loadCliente($id);
        $coaches = Coach::query()->with('usuario')->where('estado_coach', 'ACTIVO')->orderBy('id_coach')->get();

        return view('admin.clientes.edit', compact('cliente', 'coaches'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $cliente = Cliente::query()->findOrFail($id);

        $data = $request->validate([
            'ciudad' => ['nullable', 'string', 'max:80'],
            'objetivo_principal' => ['nullable', 'string', 'max:200'],
            'estado_cliente' => ['required', 'string', 'max:30'],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        $cliente->update([
            'ciudad' => $data['ciudad'] ?? null,
            'objetivo_principal' => $data['objetivo_principal'] ?? null,
            'estado_cliente' => $data['estado_cliente'],
        ]);

        if ($cliente->usuario && array_key_exists('telefono', $data)) {
            $cliente->usuario->update(['telefono' => $data['telefono']]);
        }

        return redirect()
            ->route($this->listRouteName($cliente))
            ->with('success', 'Cliente actualizado.');
    }

    public function updateCompleto(Request $request, int $id): RedirectResponse
    {
        $cliente = Cliente::query()->with('usuario')->findOrFail($id);

        $data = $request->validate([
            'ciudad' => ['nullable', 'string', 'max:80'],
            'objetivo_principal' => ['nullable', 'string', 'max:200'],
            'estado_cliente' => ['required', 'string', 'max:30'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'id_coach' => ['nullable', 'integer', 'exists:coaches,id_coach'],
            'motivo' => ['nullable', 'string', 'max:255'],
            'fecha_fin_accesos' => ['nullable', 'date'],
            'accesos' => ['nullable', 'array'],
            'accesos.*.estado_acceso' => ['required_with:accesos', 'in:ACTIVO,SUSPENDIDO,VENCIDO,INACTIVO'],
        ]);

        DB::transaction(function () use ($cliente, $data, $request) {
            $cliente->update([
                'ciudad' => $data['ciudad'] ?? null,
                'objetivo_principal' => $data['objetivo_principal'] ?? null,
                'estado_cliente' => $data['estado_cliente'],
            ]);

            if ($cliente->usuario && array_key_exists('telefono', $data)) {
                $cliente->usuario->update(['telefono' => $data['telefono']]);
            }

            if (! empty($data['id_coach'])) {
                $nuevoCoach = (int) $data['id_coach'];
                $anterior = (int) ($cliente->id_coach ?: $cliente->id_coach_asignado ?: 0);

                if ($anterior !== $nuevoCoach) {
                    $cliente->update([
                        'id_coach' => $nuevoCoach,
                        'id_coach_asignado' => $nuevoCoach,
                    ]);

                    HistorialAsignacionCoach::create([
                        'id_cliente' => $cliente->id_cliente,
                        'id_coach_anterior' => $anterior ?: null,
                        'id_coach_nuevo' => $nuevoCoach,
                        'id_admin' => Auth::id(),
                        'motivo' => $data['motivo'] ?? 'Actualización desde Admin',
                        'asignado_en' => now(),
                    ]);
                }
            }

            $fechaFinComun = $data['fecha_fin_accesos'] ?? null;

            foreach ($request->input('accesos', []) as $accesoId => $accesoData) {
                $acceso = AccesoClienteModulo::query()
                    ->where('id_cliente', $cliente->id_cliente)
                    ->where('id_acceso', (int) $accesoId)
                    ->first();

                if (! $acceso) {
                    continue;
                }

                $acceso->update([
                    'estado_acceso' => $accesoData['estado_acceso'],
                    'fecha_fin' => $fechaFinComun ?? $acceso->fecha_fin,
                ]);
            }
        });

        $nombre = trim(($cliente->usuario?->nombres ?? '').' '.($cliente->usuario?->apellidos ?? ''));

        return redirect()
            ->route($this->listRouteName($cliente))
            ->with('cliente_edit_ok', [
                'nombre' => $nombre !== '' ? $nombre : 'Cliente',
            ]);
    }

    public function asignarCoach(Request $request, int $id): RedirectResponse
    {
        $cliente = Cliente::query()->findOrFail($id);

        $data = $request->validate([
            'id_coach' => ['required', 'integer', 'exists:coaches,id_coach'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        $anterior = $cliente->id_coach ?: $cliente->id_coach_asignado;

        $cliente->update([
            'id_coach' => $data['id_coach'],
            'id_coach_asignado' => $data['id_coach'],
        ]);

        HistorialAsignacionCoach::create([
            'id_cliente' => $cliente->id_cliente,
            'id_coach_anterior' => $anterior,
            'id_coach_nuevo' => $data['id_coach'],
            'id_admin' => Auth::id(),
            'motivo' => $data['motivo'] ?? 'Asignación desde Admin',
            'asignado_en' => now(),
        ]);

        return redirect()
            ->route($this->listRouteName($cliente))
            ->with('success', 'Coach asignado al cliente.');
    }

    public function updateAcceso(Request $request, int $id, int $accesoId): RedirectResponse
    {
        $cliente = Cliente::query()->findOrFail($id);
        $acceso = AccesoClienteModulo::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('id_acceso', $accesoId)
            ->firstOrFail();

        $data = $request->validate([
            'estado_acceso' => ['required', 'in:ACTIVO,SUSPENDIDO,VENCIDO,INACTIVO'],
            'fecha_fin' => ['nullable', 'date'],
        ]);

        $acceso->update([
            'estado_acceso' => $data['estado_acceso'],
            'fecha_fin' => $data['fecha_fin'] ?? $acceso->fecha_fin,
        ]);

        return redirect()
            ->route($this->listRouteName($cliente))
            ->with('success', 'Acceso de módulo actualizado.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $cliente = Cliente::query()->findOrFail($id);
        $modalidad = $cliente->modalidad;
        $cid = $cliente->id_cliente;

        DB::transaction(function () use ($cliente, $cid) {
            HistorialAsignacionCoach::query()->where('id_cliente', $cid)->delete();

            if (Schema::hasTable('carritos') && Schema::hasTable('carrito_items')) {
                $cartIds = DB::table('carritos')->where('id_cliente', $cid)->pluck('id_carrito');
                if ($cartIds->isNotEmpty()) {
                    DB::table('carrito_items')->whereIn('id_carrito', $cartIds)->delete();
                }
            }
            if (Schema::hasTable('ordenes_tienda') && Schema::hasTable('orden_detalle')) {
                $ordenIds = DB::table('ordenes_tienda')->where('id_cliente', $cid)->pluck('id_orden');
                if ($ordenIds->isNotEmpty()) {
                    DB::table('orden_detalle')->whereIn('id_orden', $ordenIds)->delete();
                }
            }

            foreach ([
                'valoraciones_coach',
                'asistencias',
                'evento_participantes',
                'sesion_participantes',
                'participantes_grupo_whatsapp',
                'invitaciones_whatsapp',
                'progreso_videos',
                'registros_progreso',
                'fotos_progreso',
                'metas_cliente',
                'ordenes_tienda',
                'carritos',
                'acceso_cliente_modulo',
                'planes_cliente',
                'rachas_cliente',
                'historial_estados_cliente',
                'preferencias_cliente',
                'medidas_antropometricas_iniciales',
                'planes_nutricionales',
                'planes_entrenamiento',
                'registro_rutinas',
                'encuestas_satisfaccion',
                'consultas_ia_nutricion',
                'recordatorios',
            ] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->where('id_cliente', $cid)->delete();
                }
            }

            $cliente->delete();
        });

        $listRoute = $modalidad === 'PRESENCIAL'
            ? 'admin.clientes.presenciales'
            : 'admin.clientes.virtuales';

        return redirect()
            ->route($listRoute)
            ->with('cliente_ok', ['accion' => 'eliminado']);
    }

    private function loadCliente(int $id): Cliente
    {
        return Cliente::query()
            ->with(['usuario', 'coach.usuario', 'institucion', 'horario', 'planesCliente.plan', 'accesos.modulo', 'rachas'])
            ->findOrFail($id);
    }

    private function listar(Request $request, string $modalidad, string $view): View
    {
        $q = trim((string) $request->get('q', ''));

        $clientes = Cliente::query()
            ->with(['usuario', 'planesCliente.plan', 'rachas', 'institucion', 'horario', 'coach.usuario', 'accesos.modulo'])
            ->where('modalidad', $modalidad)
            ->whereHas('usuario.roles', function ($query) {
                $query->whereIn('codigo', ['CLIENTE', 'CLIENTE_INSTITUCIONAL'])
                    ->orWhereIn('codigo_rol', ['CLIENTE', 'CLIENTE_INSTITUCIONAL']);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('usuario', function ($inner) use ($q) {
                    $inner->where('nombres', 'like', "%{$q}%")
                        ->orWhere('apellidos', 'like', "%{$q}%")
                        ->orWhereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%{$q}%"]);
                });
            })
            ->orderByDesc('creado_en')
            ->paginate(10)
            ->withQueryString();

        $coaches = Coach::query()->with('usuario')->where('estado_coach', 'ACTIVO')->orderBy('id_coach')->get();

        $horarios = null;
        $plan = null;
        if ($modalidad === 'PRESENCIAL') {
            $horarios = HorarioPresencial::activos()->get();
            $plan = $this->planPresencial();
        }

        return view("admin.clientes.{$view}", compact('clientes', 'q', 'coaches', 'horarios', 'plan'));
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

    private function listRouteName(Cliente $cliente): string
    {
        return $cliente->modalidad === 'PRESENCIAL'
            ? 'admin.clientes.presenciales'
            : 'admin.clientes.virtuales';
    }
}
