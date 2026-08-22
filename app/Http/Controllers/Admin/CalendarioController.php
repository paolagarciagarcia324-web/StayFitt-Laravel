<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\Evento;
use App\Models\Notificacion;
use App\Models\Sesion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CalendarioController extends Controller
{
    public function index(Request $request): View
    {
        $mes = Carbon::createFromFormat('Y-m', $request->query('mes', now()->format('Y-m')))
            ->startOfMonth();
        $finMes = $mes->copy()->endOfMonth();
        $hoy = Carbon::now()->startOfDay();
        $horizonte = $hoy->copy()->addDays(21)->endOfDay();

        $eventosMes = Evento::query()
            ->where(function ($q) use ($mes, $finMes) {
                $q->whereBetween('fecha_inicio', [$mes, $finMes])
                    ->orWhereBetween('fecha_fin', [$mes, $finMes])
                    ->orWhere(function ($q2) use ($mes, $finMes) {
                        $q2->where('fecha_inicio', '<=', $mes)
                            ->where(function ($q3) use ($finMes) {
                                $q3->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $finMes);
                            });
                    });
            })
            ->orderBy('fecha_inicio')
            ->get();

        $eventosPorDia = $this->mapEventosPorDia($eventosMes, $mes->copy()->startOfWeek(Carbon::MONDAY), $finMes->copy()->endOfWeek(Carbon::SUNDAY));

        $sesionesMes = collect();
        $sesionesArchivo = collect();
        if (Schema::hasTable('sesiones')) {
            $sesionesMes = Sesion::query()
                ->with('coach.usuario')
                ->whereBetween('fecha', [$mes->toDateString(), $finMes->toDateString()])
                ->orderBy('fecha')
                ->orderBy('hora_inicio')
                ->limit(80)
                ->get();

            $archivoDesde = now()->copy()->subMonths(5)->startOfMonth();
            $archivoHasta = now()->copy()->addMonths(2)->endOfMonth();
            $sesionesArchivo = Sesion::query()
                ->with('coach.usuario')
                ->whereBetween('fecha', [$archivoDesde->toDateString(), $archivoHasta->toDateString()])
                ->orderByDesc('fecha')
                ->orderBy('hora_inicio')
                ->limit(200)
                ->get();
        }

        $sesionesPorDia = $sesionesMes->groupBy(function ($s) {
            $fecha = $s->fecha instanceof Carbon ? $s->fecha : Carbon::parse($s->fecha);

            return $fecha->format('Y-m-d');
        });

        $celdas = [];
        $inicioGrid = $mes->copy()->startOfWeek(Carbon::MONDAY);
        $finGrid = $finMes->copy()->endOfWeek(Carbon::SUNDAY);
        for ($d = $inicioGrid->copy(); $d->lte($finGrid); $d->addDay()) {
            $key = $d->toDateString();
            $evs = $eventosPorDia->get($key, collect());
            $sess = $sesionesPorDia->get($key, collect());
            $celdas[] = [
                'fecha' => $d->copy(),
                'enMes' => $d->month === $mes->month,
                'hoy' => $d->isToday(),
                'eventos' => $evs,
                'sesiones' => $sess,
                'hasItems' => $evs->isNotEmpty() || $sess->isNotEmpty(),
            ];
        }

        $coaches = Coach::query()
            ->with('usuario')
            ->orderBy('id_coach')
            ->get();

        $proximosEventos = Evento::query()
            ->where('fecha_inicio', '>=', $hoy)
            ->where('fecha_inicio', '<=', $horizonte)
            ->orderBy('fecha_inicio')
            ->limit(12)
            ->get();

        $proximasSesiones = Schema::hasTable('sesiones')
            ? Sesion::query()
                ->with('coach.usuario')
                ->where('fecha', '>=', $hoy->toDateString())
                ->where('fecha', '<=', $horizonte->toDateString())
                ->orderBy('fecha')
                ->orderBy('hora_inicio')
                ->limit(12)
                ->get()
            : collect();

        $recordatorios = $this->buildRecordatorios($proximosEventos, $proximasSesiones);
        $this->purgeExpiredReminders();
        $this->syncAdminReminders($proximosEventos, $proximasSesiones);

        $mesKeyActual = now()->format('Y-m');
        $sesionesPorMes = $sesionesArchivo
            ->groupBy(function ($s) {
                $fecha = $s->fecha instanceof Carbon ? $s->fecha : Carbon::parse($s->fecha);

                return $fecha->format('Y-m');
            })
            ->sortKeysDesc()
            ->map(function (Collection $items, string $key) use ($mesKeyActual) {
                $label = Carbon::createFromFormat('Y-m', $key)->locale('es')->translatedFormat('F Y');

                return [
                    'key' => $key,
                    'label' => ucfirst($label),
                    'count' => $items->count(),
                    'is_current' => $key === $mesKeyActual,
                    'sesiones' => $items->sortBy(function ($s) {
                        $fecha = $s->fecha instanceof Carbon ? $s->fecha->format('Y-m-d') : Carbon::parse($s->fecha)->format('Y-m-d');

                        return $fecha.' '.substr((string) $s->hora_inicio, 0, 5);
                    })->values(),
                ];
            })
            ->values();

        $mesAnterior = $mes->copy()->subMonth()->format('Y-m');
        $mesSiguiente = $mes->copy()->addMonth()->format('Y-m');
        $eventos = $eventosMes;
        $sesiones = $sesionesMes;
        $recordatoriosCount = $recordatorios->count();

        return view('admin.calendario.index', compact(
            'eventos',
            'sesiones',
            'sesionesPorMes',
            'mes',
            'celdas',
            'coaches',
            'recordatorios',
            'recordatoriosCount',
            'mesAnterior',
            'mesSiguiente'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateSesion($request);
        $data['hora_inicio'] = strlen($data['hora_inicio']) === 5 ? $data['hora_inicio'].':00' : $data['hora_inicio'];
        $data['hora_fin'] = strlen($data['hora_fin']) === 5 ? $data['hora_fin'].':00' : $data['hora_fin'];

        $sesion = Sesion::create([
            ...$data,
            'cupo_reservado' => 0,
        ]);

        $this->notifyAdmin(
            'SESION',
            'Nueva sesión en calendario',
            "Se programó «{$sesion->titulo}» para el ".Carbon::parse($sesion->fecha)->format('d/m/Y').'.',
            $sesion->id_sesion
        );

        return back()->with('sesion_ok', ['accion' => 'creada']);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $sesion = Sesion::query()->findOrFail($id);
        $data = $this->validateSesion($request, $id);
        $data['hora_inicio'] = strlen($data['hora_inicio']) === 5 ? $data['hora_inicio'].':00' : $data['hora_inicio'];
        $data['hora_fin'] = strlen($data['hora_fin']) === 5 ? $data['hora_fin'].':00' : $data['hora_fin'];

        $sesion->update($data);

        $this->notifyAdmin(
            'SESION',
            'Sesión actualizada',
            "Se actualizó «{$sesion->titulo}» ({$sesion->fecha->format('d/m/Y')}).",
            $sesion->id_sesion
        );

        return back()->with('sesion_ok', ['accion' => 'actualizada']);
    }

    public function destroy(int $id): RedirectResponse
    {
        $sesion = Sesion::query()->findOrFail($id);
        $titulo = $sesion->titulo;
        $sesion->delete();

        $this->notifyAdmin(
            'SESION',
            'Sesión eliminada',
            "Se eliminó «{$titulo}» del calendario.",
            $id
        );

        return back()->with('sesion_ok', ['accion' => 'eliminada']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSesion(Request $request, ?int $ignoreId = null): array
    {
        $horaInicio = substr((string) $request->input('hora_inicio'), 0, 5);

        $uniqueRule = function (string $attribute, mixed $value, \Closure $fail) use ($request, $horaInicio, $ignoreId) {
            $q = Sesion::query()
                ->where('id_coach', (int) $request->input('id_coach'))
                ->whereDate('fecha', $request->input('fecha'))
                ->whereRaw('LEFT(CAST(hora_inicio AS CHAR), 5) = ?', [$horaInicio]);
            if ($ignoreId) {
                $q->where('id_sesion', '!=', $ignoreId);
            }
            if ($q->exists()) {
                $fail('Ese coach ya tiene una sesión a esa fecha y hora. Elige otra hora u otro coach.');
            }
        };

        return $request->validate([
            'id_coach' => ['required', 'integer', 'exists:coaches,id_coach'],
            'tipo' => ['required', 'in:INDIVIDUAL,GRUPAL'],
            'titulo' => ['required', 'string', 'max:150'],
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/', $uniqueRule],
            'hora_fin' => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/', 'after:hora_inicio'],
            'modalidad' => ['required', 'in:PRESENCIAL,VIRTUAL'],
            'cupo_maximo' => ['required', 'integer', 'min:1'],
            'estado' => ['required', 'in:PROGRAMADA,EN_CURSO,COMPLETADA,CANCELADA'],
        ], [
            'id_coach.required' => 'Selecciona un coach.',
            'titulo.required' => 'El título es obligatorio.',
            'fecha.required' => 'La fecha es obligatoria.',
            'hora_inicio.required' => 'La hora de inicio es obligatoria.',
            'hora_fin.required' => 'La hora de fin es obligatoria.',
            'hora_fin.after' => 'La hora de fin debe ser posterior al inicio.',
            'cupo_maximo.required' => 'El cupo máximo es obligatorio.',
            'tipo.required' => 'El tipo es obligatorio.',
            'modalidad.required' => 'La modalidad es obligatoria.',
            'estado.required' => 'El estado es obligatorio.',
        ]);
    }

    /**
     * Pinta el evento en cada día entre inicio y fin (o solo inicio).
     *
     * @return Collection<string, Collection<int, Evento>>
     */
    private function mapEventosPorDia(Collection $eventos, Carbon $desde, Carbon $hasta): Collection
    {
        $map = collect();

        foreach ($eventos as $evento) {
            if (! $evento->fecha_inicio) {
                continue;
            }
            $start = $evento->fecha_inicio->copy()->startOfDay();
            $end = $evento->fecha_fin
                ? $evento->fecha_fin->copy()->startOfDay()
                : $start->copy();
            if ($end->lt($start)) {
                $end = $start->copy();
            }
            if ($start->lt($desde)) {
                $start = $desde->copy();
            }
            if ($end->gt($hasta)) {
                $end = $hasta->copy();
            }
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $key = $d->toDateString();
                if (! $map->has($key)) {
                    $map->put($key, collect());
                }
                $map->get($key)->push($evento);
            }
        }

        return $map;
    }

    /**
     * @return Collection<int, array{tipo:string,titulo:string,cuando:string,detalle:string,urgencia:string,href:?string}>
     */
    private function buildRecordatorios(Collection $eventos, Collection $sesiones): Collection
    {
        $items = collect();
        $now = Carbon::now();

        foreach ($eventos as $evento) {
            $inicio = $evento->fecha_inicio;
            if (! $inicio || $inicio->lt($now)) {
                continue;
            }
            $diff = $now->diffInHours($inicio, false);
            $urgencia = $diff <= 24 ? 'alta' : ($diff <= 72 ? 'media' : 'baja');
            $items->push([
                'tipo' => 'evento',
                'titulo' => $evento->nombre,
                'cuando' => $inicio->format('d/m/Y').' '.\App\Models\HorarioPresencial::formatoHora12($inicio->format('H:i')),
                'detalle' => trim(($evento->modalidad ?: '').' · '.($evento->estado ?: '')),
                'urgencia' => $urgencia,
                'sort' => $inicio->timestamp,
                'href' => route('admin.eventos.index'),
            ]);
        }

        foreach ($sesiones as $sesion) {
            $fecha = $sesion->fecha instanceof Carbon ? $sesion->fecha->copy() : Carbon::parse($sesion->fecha);
            $hi = substr((string) $sesion->hora_inicio, 0, 5);
            $stamp = Carbon::parse($fecha->toDateString().' '.$hi);
            if ($stamp->lt($now)) {
                continue;
            }
            $diff = $now->diffInHours($stamp, false);
            $urgencia = $diff <= 24 ? 'alta' : ($diff <= 72 ? 'media' : 'baja');
            $items->push([
                'tipo' => 'sesion',
                'titulo' => $sesion->titulo,
                'cuando' => $fecha->format('d/m/Y').' '.$sesion->horaInicioCorta(),
                'detalle' => trim(($sesion->modalidad ?: '').' · '.($sesion->estado ?: '')),
                'urgencia' => $urgencia,
                'sort' => $stamp->timestamp,
                'href' => null,
            ]);
        }

        return $items->sortBy('sort')->values()->take(12);
    }

    private function purgeExpiredReminders(): void
    {
        if (! Schema::hasTable('notificaciones')) {
            return;
        }

        $adminId = Auth::id();
        if (! $adminId) {
            return;
        }

        $now = Carbon::now();

        Notificacion::query()
            ->where('id_user', $adminId)
            ->where('tipo', 'RECORDATORIO')
            ->where('referencia_tipo', 'SESION')
            ->whereNotNull('referencia_id')
            ->get()
            ->each(function (Notificacion $n) use ($now) {
                $sesion = Sesion::query()->find($n->referencia_id);
                if (! $sesion) {
                    $n->delete();

                    return;
                }
                $fecha = $sesion->fecha instanceof Carbon ? $sesion->fecha->copy() : Carbon::parse($sesion->fecha);
                $hi = substr((string) $sesion->hora_inicio, 0, 5);
                $stamp = Carbon::parse($fecha->toDateString().' '.$hi);
                if ($stamp->lt($now)) {
                    $n->delete();
                }
            });

        Notificacion::query()
            ->where('id_user', $adminId)
            ->where('tipo', 'RECORDATORIO')
            ->where('referencia_tipo', 'EVENTO')
            ->whereNotNull('referencia_id')
            ->get()
            ->each(function (Notificacion $n) use ($now) {
                $evento = Evento::query()->find($n->referencia_id);
                if (! $evento || ! $evento->fecha_inicio || $evento->fecha_inicio->lt($now)) {
                    $n->delete();
                }
            });
    }

    private function syncAdminReminders(Collection $eventos, Collection $sesiones): void
    {
        if (! Schema::hasTable('notificaciones')) {
            return;
        }

        $adminId = Auth::id();
        if (! $adminId) {
            return;
        }

        $now = Carbon::now();

        foreach ($eventos as $evento) {
            if (! $evento->fecha_inicio || $evento->estado === 'CANCELADO') {
                continue;
            }
            $hours = $now->diffInHours($evento->fecha_inicio, false);
            if ($hours < 0 || $hours > 72) {
                continue;
            }
            $exists = Notificacion::query()
                ->where('id_user', $adminId)
                ->where('referencia_tipo', 'EVENTO')
                ->where('referencia_id', $evento->id_evento)
                ->where('tipo', 'RECORDATORIO')
                ->where('creado_en', '>=', $now->copy()->subDays(2))
                ->exists();
            if ($exists) {
                continue;
            }
            Notificacion::create([
                'id_user' => $adminId,
                'tipo' => 'RECORDATORIO',
                'titulo' => 'Recordatorio de evento',
                'mensaje' => "«{$evento->nombre}» es el ".$evento->fecha_inicio->format('d/m/Y H:i').'.',
                'leida' => false,
                'canal' => 'PANEL',
                'referencia_tipo' => 'EVENTO',
                'referencia_id' => $evento->id_evento,
                'creado_en' => $now,
            ]);
        }

        foreach ($sesiones as $sesion) {
            $fecha = $sesion->fecha instanceof Carbon ? $sesion->fecha->copy() : Carbon::parse($sesion->fecha);
            $hi = substr((string) $sesion->hora_inicio, 0, 5);
            $stamp = Carbon::parse($fecha->toDateString().' '.$hi);
            $hours = $now->diffInHours($stamp, false);
            if ($hours < 0 || $hours > 72) {
                continue;
            }
            $exists = Notificacion::query()
                ->where('id_user', $adminId)
                ->where('referencia_tipo', 'SESION')
                ->where('referencia_id', $sesion->id_sesion)
                ->where('tipo', 'RECORDATORIO')
                ->where('creado_en', '>=', $now->copy()->subDays(2))
                ->exists();
            if ($exists) {
                continue;
            }
            Notificacion::create([
                'id_user' => $adminId,
                'tipo' => 'RECORDATORIO',
                'titulo' => 'Recordatorio de sesión',
                'mensaje' => "«{$sesion->titulo}» es el ".$fecha->format('d/m/Y')." a las {$hi}.",
                'leida' => false,
                'canal' => 'PANEL',
                'referencia_tipo' => 'SESION',
                'referencia_id' => $sesion->id_sesion,
                'creado_en' => $now,
            ]);
        }
    }

    private function notifyAdmin(string $refTipo, string $titulo, string $mensaje, int $refId): void
    {
        if (! Schema::hasTable('notificaciones')) {
            return;
        }
        $adminId = Auth::id();
        if (! $adminId) {
            return;
        }
        Notificacion::create([
            'id_user' => $adminId,
            'tipo' => 'CALENDARIO',
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'leida' => false,
            'canal' => 'PANEL',
            'referencia_tipo' => $refTipo,
            'referencia_id' => $refId,
            'creado_en' => now(),
        ]);
    }
}
