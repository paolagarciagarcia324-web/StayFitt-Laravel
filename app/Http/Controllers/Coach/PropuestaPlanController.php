<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\CategoriaVideo;
use App\Models\Coach;
use App\Models\ProgramaVirtual;
use App\Models\PropuestaPlan;
use App\Support\ResolvesStaffCoach;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PropuestaPlanController extends Controller
{
    use ResolvesStaffCoach;

    public function index(Request $request): View
    {
        $user = Auth::user();
        $q = trim((string) $request->get('q', ''));
        $estado = trim((string) $request->get('estado', ''));

        $propuestas = PropuestaPlan::query()
            ->with(['coach.usuario', 'programa'])
            ->when(
                $user->tieneRol('COACH') && ! $user->tieneRol('ASISTENTE_TECNICO') && ! $user->tieneRol('ADMIN'),
                function ($query) use ($user) {
                    $coachId = $user->coach?->id_coach;
                    $query->where('id_coach', $coachId ?: 0);
                }
            )
            ->when($q !== '', fn ($query) => $query->where('titulo_interno', 'like', "%{$q}%"))
            ->when($estado !== '', fn ($query) => $query->where('estado', $estado))
            ->orderByDesc('id_propuesta')
            ->get();

        return view('coach.propuestas.index', compact('propuestas', 'q', 'estado'));
    }

    public function create(): View
    {
        $user = Auth::user();
        $coaches = Coach::with('usuario')->orderBy('id_coach')->get();
        $mustPickCoach = $user->tieneRol('ASISTENTE_TECNICO') || $user->tieneRol('ADMIN');

        return view('coach.propuestas.create', compact('coaches', 'mustPickCoach'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->validate([
            'titulo_interno' => ['required', 'string', 'max:150'],
            'descripcion_interna' => ['nullable', 'string'],
            'id_coach' => ['nullable', 'integer', 'exists:coaches,id_coach'],
            'notas_coach' => ['nullable', 'string'],
        ]);

        $coachId = $this->resolveCoachIdForUser($user, isset($data['id_coach']) ? (int) $data['id_coach'] : null);

        $propuesta = DB::transaction(function () use ($data, $user, $coachId) {
            $programa = ProgramaVirtual::create([
                'nombre' => $data['titulo_interno'],
                'descripcion' => $data['descripcion_interna'] ?? null,
                'id_plan' => null,
                'estado' => 'BORRADOR',
            ]);

            CategoriaVideo::create([
                'id_programa' => $programa->id_programa,
                'nombre' => 'Clases diarias',
                'orden' => 1,
            ]);

            $propuesta = PropuestaPlan::create([
                'id_coach' => $coachId,
                'creado_por' => $user->id_user,
                'id_programa' => $programa->id_programa,
                'titulo_interno' => $data['titulo_interno'],
                'descripcion_interna' => $data['descripcion_interna'] ?? null,
                'notas_coach' => $data['notas_coach'] ?? null,
                'estado' => PropuestaPlan::ESTADO_BORRADOR_ENTRENAMIENTO,
            ]);

            $programa->update(['id_propuesta' => $propuesta->id_propuesta]);

            return $propuesta;
        });

        return redirect()
            ->route('coach.propuestas.entrenamiento', $propuesta)
            ->with('success', 'Propuesta creada. Ahora agrega las clases en video.');
    }

    public function entrenamiento(PropuestaPlan $propuesta): View|RedirectResponse
    {
        $this->authorizePropuesta($propuesta);
        $propuesta->load(['programa.categorias.videos', 'coach.usuario']);

        return view('coach.propuestas.entrenamiento', [
            'propuesta' => $propuesta,
            'videos' => $propuesta->programa?->categorias->flatMap(fn ($c) => $c->videos)->sortBy('dia_orden') ?? collect(),
            'step' => 1,
        ]);
    }

    public function nutricion(PropuestaPlan $propuesta): View|RedirectResponse
    {
        $this->authorizePropuesta($propuesta);

        if ($propuesta->estado === PropuestaPlan::ESTADO_BORRADOR_ENTRENAMIENTO) {
            if (! $propuesta->puedeAvanzarANutricion()) {
                return redirect()
                    ->route('coach.propuestas.entrenamiento', $propuesta)
                    ->with('error', 'Agrega al menos un video antes de pasar a nutrición.');
            }

            $this->ensureNutricion($propuesta);
            $propuesta->update([
                'estado' => PropuestaPlan::ESTADO_BORRADOR_NUTRICION,
                'actualizado_en' => now(),
            ]);
        }

        $propuesta->load(['planNutricional.comidas', 'coach.usuario']);

        return view('coach.propuestas.nutricion', [
            'propuesta' => $propuesta,
            'nutricion' => $propuesta->planNutricional,
            'step' => 2,
        ]);
    }

    public function resumen(PropuestaPlan $propuesta): View|RedirectResponse
    {
        $this->authorizePropuesta($propuesta);
        $propuesta->load(['programa.categorias.videos', 'planNutricional.comidas', 'coach.usuario']);

        return view('coach.propuestas.resumen', [
            'propuesta' => $propuesta,
            'videos' => $propuesta->programa?->categorias->flatMap(fn ($c) => $c->videos)->sortBy('dia_orden') ?? collect(),
            'nutricion' => $propuesta->planNutricional,
            'step' => 3,
        ]);
    }

    public function enviar(PropuestaPlan $propuesta): RedirectResponse
    {
        $this->authorizePropuesta($propuesta);

        if (! $propuesta->esEditable() && $propuesta->estado !== PropuestaPlan::ESTADO_BORRADOR_NUTRICION) {
            if ($propuesta->estado === PropuestaPlan::ESTADO_PENDIENTE_REVISION) {
                return redirect()->route('coach.propuestas.resumen', $propuesta)
                    ->with('error', 'Esta propuesta ya está en revisión.');
            }
        }

        $propuesta->load('planNutricional.comidas');

        if (! $propuesta->puedeEnviarARevision()) {
            return redirect()
                ->route('coach.propuestas.nutricion', $propuesta)
                ->with('error', 'Completa nutrición (nombre, objetivo y al menos una comida) antes de enviar.');
        }

        $propuesta->update([
            'estado' => PropuestaPlan::ESTADO_PENDIENTE_REVISION,
            'enviado_en' => now(),
            'motivo_rechazo' => null,
            'actualizado_en' => now(),
        ]);

        return redirect()
            ->route('coach.propuestas.index')
            ->with('success', 'Propuesta enviada a revisión del administrador.');
    }

    public function updateMeta(Request $request, PropuestaPlan $propuesta): RedirectResponse
    {
        $this->authorizePropuesta($propuesta);

        if (! $propuesta->esEditable()) {
            return back()->with('error', 'Esta propuesta no se puede editar en su estado actual.');
        }

        $data = $request->validate([
            'titulo_interno' => ['required', 'string', 'max:150'],
            'descripcion_interna' => ['nullable', 'string'],
            'notas_coach' => ['nullable', 'string'],
        ]);

        $propuesta->update($data + ['actualizado_en' => now()]);
        $propuesta->programa?->update([
            'nombre' => $data['titulo_interno'],
            'descripcion' => $data['descripcion_interna'] ?? null,
            'actualizado_en' => now(),
        ]);

        return back()->with('success', 'Datos de la propuesta actualizados.');
    }

    private function authorizePropuesta(PropuestaPlan $propuesta): void
    {
        if (! $this->userCanAccessPropuesta(Auth::user(), $propuesta)) {
            abort(403);
        }
    }

    private function ensureNutricion(PropuestaPlan $propuesta): void
    {
        if ($propuesta->id_plan_nutricional) {
            return;
        }

        $nutricion = \App\Models\PlanNutricional::create([
            'id_cliente' => null,
            'id_coach' => $propuesta->id_coach,
            'id_propuesta' => $propuesta->id_propuesta,
            'nombre' => 'Nutrición · '.$propuesta->titulo_interno,
            'objetivo' => '',
            'estado' => 'BORRADOR',
        ]);

        $propuesta->update([
            'id_plan_nutricional' => $nutricion->id_plan_nutricional,
            'actualizado_en' => now(),
        ]);
    }
}
