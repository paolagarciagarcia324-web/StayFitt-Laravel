<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Notificacion;
use App\Support\ResolvesAdminMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventoController extends Controller
{
    use ResolvesAdminMedia;

    public function index(Request $request): View
    {
        $now = now();
        $mesRaw = trim((string) $request->get('mes', $now->format('Y-m')));
        $mes = preg_match('/^\d{4}-\d{2}$/', $mesRaw) ? $mesRaw : $now->format('Y-m');
        $mesInicio = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
        $mesFin = $mesInicio->copy()->endOfMonth();

        $eventos = Evento::query()
            ->where(function ($q) use ($mesInicio, $mesFin) {
                $q->whereBetween('fecha_inicio', [$mesInicio, $mesFin])
                    ->orWhere(function ($inner) use ($mesInicio, $mesFin) {
                        $inner->whereNotNull('fecha_fin')
                            ->where('fecha_inicio', '<=', $mesFin)
                            ->where('fecha_fin', '>=', $mesInicio);
                    });
            })
            ->orderByDesc('fecha_inicio')
            ->paginate(10)
            ->withQueryString();

        $enMes = Evento::query()->where(function ($q) use ($mesInicio, $mesFin) {
            $q->whereBetween('fecha_inicio', [$mesInicio, $mesFin])
                ->orWhere(function ($inner) use ($mesInicio, $mesFin) {
                    $inner->whereNotNull('fecha_fin')
                        ->where('fecha_inicio', '<=', $mesFin)
                        ->where('fecha_fin', '>=', $mesInicio);
                });
        });

        $stats = [
            'en_curso' => (clone $enMes)
                ->where('fecha_inicio', '<=', $now)
                ->where(function ($q) use ($now) {
                    $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $now);
                })
                ->count(),
            'proximos' => (clone $enMes)->where('fecha_inicio', '>', $now)->count(),
            'cerrados' => (clone $enMes)->whereNotNull('fecha_fin')->where('fecha_fin', '<', $now)->count(),
        ];

        $hoy = $now->copy()->startOfDay();
        $anclaSemana = $hoy->betweenIncluded($mesInicio, $mesFin)
            ? $hoy->copy()
            : $mesInicio->copy();
        $semanaInicio = $anclaSemana->copy()->startOfWeek(Carbon::MONDAY);
        if ($semanaInicio->lt($mesInicio)) {
            $semanaInicio = $mesInicio->copy();
        }
        $semana = collect();
        $cursorSemana = $semanaInicio->copy();
        while ($semana->count() < 7 && $cursorSemana->lte($mesFin)) {
            $semana->push($cursorSemana->copy());
            $cursorSemana->addDay();
        }

        $eventosDelMes = (clone $enMes)->get(['id_evento', 'fecha_inicio', 'fecha_fin']);
        $diasConEvento = $eventosDelMes
            ->map(fn ($e) => optional($e->fecha_inicio)->format('Y-m-d'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $eventosPorDia = [];
        foreach ($eventosDelMes as $e) {
            $key = optional($e->fecha_inicio)->format('Y-m-d');
            if (! $key) {
                continue;
            }
            $fin = $e->fecha_fin ?: $e->fecha_inicio;
            $vencido = $fin && $fin->lt($now);
            $eventosPorDia[$key] ??= [];
            $eventosPorDia[$key][] = [
                'id' => (int) $e->id_evento,
                'vencido' => (bool) $vencido,
            ];
        }

        $mesAnterior = $mesInicio->copy()->subMonth()->format('Y-m');
        $mesSiguiente = $mesInicio->copy()->addMonth()->format('Y-m');
        $mesLabel = ucfirst($mesInicio->locale('es')->translatedFormat('F Y'));

        return view('admin.eventos.index', compact(
            'eventos',
            'stats',
            'semana',
            'diasConEvento',
            'eventosPorDia',
            'mes',
            'mesAnterior',
            'mesSiguiente',
            'mesLabel'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateEvento($request);

        $evento = Evento::create([
            ...collect($data)->except(['imagen'])->all(),
            'imagen_url' => $this->resolveMedia($request, 'imagen', 'eventos', 'imagen_url'),
            'id_admin' => Auth::id(),
            'cupo_inscritos' => 0,
        ]);

        $this->notifyAdminCalendario(
            'EVENTO',
            'Nuevo evento en calendario',
            "Se creó «{$evento->nombre}» para el ".optional($evento->fecha_inicio)->format('d/m/Y H:i').'.',
            (int) $evento->id_evento
        );

        return back()->with('evento_ok', ['accion' => 'creado']);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $evento = Evento::query()->findOrFail($id);

        $data = $this->validateEvento($request, $evento);

        $evento->update([
            ...collect($data)->except(['imagen'])->all(),
            'imagen_url' => $this->resolveMedia($request, 'imagen', 'eventos', 'imagen_url', $evento->imagen_url),
        ]);

        $this->notifyAdminCalendario(
            'EVENTO',
            'Evento actualizado',
            "Se actualizó «{$evento->nombre}» ({$evento->fecha_inicio?->format('d/m/Y H:i')}).",
            (int) $evento->id_evento
        );

        return back()->with('evento_ok', ['accion' => 'actualizado']);
    }

    public function destroy(int $id): RedirectResponse
    {
        $evento = Evento::query()->findOrFail($id);
        $nombre = $evento->nombre;
        $evento->delete();

        $this->notifyAdminCalendario(
            'EVENTO',
            'Evento eliminado',
            "Se eliminó «{$nombre}» del calendario.",
            $id
        );

        return back()->with('evento_ok', ['accion' => 'eliminado']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateEvento(Request $request, ?Evento $evento = null): array
    {
        $hasExistingCover = $evento && filled($evento->imagen_url);

        return $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['required', 'string'],
            'tipo' => ['required', 'string', 'max:30'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'modalidad' => ['required', 'in:VIRTUAL,PRESENCIAL,MIXTO'],
            'cupo_maximo' => ['required', 'integer', 'min:1'],
            'estado' => ['required', 'in:BORRADOR,PUBLICADO,CERRADO,CANCELADO'],
            'imagen_url' => [
                Rule::requiredIf(fn () => ! $request->hasFile('imagen') && ! $hasExistingCover),
                'nullable',
                'string',
                'max:500',
            ],
            'imagen' => [
                Rule::requiredIf(fn () => blank($request->input('imagen_url')) && ! $hasExistingCover),
                'nullable',
                'image',
                'max:4096',
            ],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior al inicio.',
            'cupo_maximo.required' => 'El cupo máximo es obligatorio.',
            'imagen.required' => 'Sube una foto o indica una URL de portada.',
            'imagen_url.required' => 'Sube una foto o indica una URL de portada.',
        ]);
    }

    private function notifyAdminCalendario(string $refTipo, string $titulo, string $mensaje, int $refId): void
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
            'mensaje' => \Illuminate\Support\Str::limit($mensaje, 490),
            'leida' => false,
            'canal' => 'PANEL',
            'referencia_tipo' => $refTipo,
            'referencia_id' => $refId,
            'creado_en' => now(),
        ]);
    }
}
