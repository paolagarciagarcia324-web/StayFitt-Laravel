<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuloServicio;
use App\Models\Plan;
use App\Models\PropuestaPlan;
use App\Support\ResolvesAdminMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PropuestaPlanController extends Controller
{
    use ResolvesAdminMedia;

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $estado = trim((string) $request->get('estado', PropuestaPlan::ESTADO_PENDIENTE_REVISION));

        $propuestas = PropuestaPlan::query()
            ->with(['coach.usuario', 'programa', 'planNutricional'])
            ->when($q !== '', fn ($query) => $query->where('titulo_interno', 'like', "%{$q}%"))
            ->when($estado !== '' && $estado !== 'TODOS', fn ($query) => $query->where('estado', $estado))
            ->orderByDesc('enviado_en')
            ->orderByDesc('id_propuesta')
            ->get();

        $pendientes = PropuestaPlan::query()
            ->where('estado', PropuestaPlan::ESTADO_PENDIENTE_REVISION)
            ->count();

        return view('admin.propuestas.index', compact('propuestas', 'q', 'estado', 'pendientes'));
    }

    public function show(PropuestaPlan $propuesta): View
    {
        $propuesta->load([
            'coach.usuario',
            'programa.categorias.videos',
            'planNutricional.comidas',
            'planPublicado',
        ]);

        $videos = $propuesta->programa?->categorias->flatMap(fn ($c) => $c->videos)->sortBy('dia_orden') ?? collect();

        return view('admin.propuestas.show', compact('propuesta', 'videos'));
    }

    public function publicarForm(PropuestaPlan $propuesta): View|RedirectResponse
    {
        if ($propuesta->estado !== PropuestaPlan::ESTADO_PENDIENTE_REVISION
            && $propuesta->estado !== PropuestaPlan::ESTADO_APROBADO) {
            return redirect()
                ->route('admin.propuestas.show', $propuesta)
                ->with('error', 'Solo se pueden publicar propuestas pendientes o aprobadas.');
        }

        $propuesta->load(['programa', 'planNutricional']);
        $modulos = ModuloServicio::orderBy('orden')->get();
        $plan = new Plan([
            'nombre' => $propuesta->titulo_interno,
            'descripcion' => $propuesta->descripcion_interna,
            'modalidad' => 'VIRTUAL',
            'tipo_cliente' => 'INDIVIDUAL',
            'moneda' => 'COP',
            'duracion_dias' => max(1, $propuesta->conteoVideos()),
            'estado_plan' => 'ACTIVO',
            'requiere_coach' => true,
            'cupos_totales' => 50,
            'dias_gracia' => 3,
        ]);

        return view('admin.propuestas.publicar', compact('propuesta', 'plan', 'modulos'));
    }

    public function publicar(Request $request, PropuestaPlan $propuesta): RedirectResponse
    {
        if (! in_array($propuesta->estado, [
            PropuestaPlan::ESTADO_PENDIENTE_REVISION,
            PropuestaPlan::ESTADO_APROBADO,
        ], true)) {
            return back()->with('error', 'Esta propuesta no se puede publicar.');
        }

        $data = $this->validatedPlan($request);
        $data['slug'] = Str::slug($data['nombre']).'-'.Str::lower(Str::random(4));
        $data['caracteristicas_json'] = $this->parseFeatures($request->input('caracteristicas'));
        $data['creado_por'] = Auth::id();
        $data['cupos_reservados'] = 0;
        $data['cupos_ocupados'] = 0;
        $data['imagen_url'] = $this->resolveMedia($request, 'imagen', 'planes', 'imagen_url');

        DB::transaction(function () use ($request, $propuesta, $data) {
            $plan = Plan::create($data);
            $plan->modulos()->sync($this->modulosSync($request->input('modulos', [])));

            $propuesta->programa?->update([
                'id_plan' => $plan->id_plan,
                'nombre' => $plan->nombre,
                'descripcion' => $plan->descripcion,
                'estado' => 'ACTIVO',
                'actualizado_en' => now(),
            ]);

            $propuesta->planNutricional?->update([
                'nombre' => 'Nutrición · '.$plan->nombre,
                'estado' => 'ACTIVO',
                'actualizado_en' => now(),
            ]);

            $propuesta->update([
                'id_plan_publicado' => $plan->id_plan,
                'estado' => PropuestaPlan::ESTADO_PUBLICADO,
                'revisado_en' => now(),
                'publicado_en' => now(),
                'notas_admin' => $request->input('notas_admin'),
                'actualizado_en' => now(),
            ]);
        });

        return redirect()
            ->route('admin.propuestas.index')
            ->with('success', 'Plan publicado en el catálogo welcome.');
    }

    public function rechazar(Request $request, PropuestaPlan $propuesta): RedirectResponse
    {
        $data = $request->validate([
            'motivo_rechazo' => ['required', 'string', 'max:2000'],
        ]);

        if ($propuesta->estado !== PropuestaPlan::ESTADO_PENDIENTE_REVISION) {
            return back()->with('error', 'Solo se pueden rechazar propuestas pendientes.');
        }

        $propuesta->update([
            'estado' => PropuestaPlan::ESTADO_RECHAZADO,
            'motivo_rechazo' => $data['motivo_rechazo'],
            'revisado_en' => now(),
            'actualizado_en' => now(),
        ]);

        return redirect()
            ->route('admin.propuestas.index')
            ->with('success', 'Propuesta rechazada. El coach puede corregirla y reenviarla.');
    }

    private function validatedPlan(Request $request): array
    {
        $data = $request->validate([
            'codigo' => [
                'nullable',
                'string',
                'max:40',
                Rule::unique('planes', 'codigo'),
            ],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'imagen_url' => ['nullable', 'string', 'max:500'],
            'imagen' => ['nullable', 'image', 'max:4096'],
            'inclusiones' => ['nullable', 'string'],
            'beneficios' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'moneda' => ['required', 'string', 'size:3'],
            'duracion_dias' => ['required', 'integer', 'min:1'],
            'modalidad' => ['required', 'in:VIRTUAL,PRESENCIAL,MIXTO'],
            'tipo_cliente' => ['required', 'in:INDIVIDUAL,INSTITUCIONAL,AMBOS'],
            'cupos_totales' => ['required', 'integer', 'min:0'],
            'cupo_maximo' => ['nullable', 'integer', 'min:1'],
            'dias_gracia' => ['nullable', 'integer', 'min:0', 'max:30'],
            'orden' => ['nullable', 'integer'],
            'etiqueta' => ['nullable', 'string', 'max:50'],
            'estado_plan' => ['required', 'in:ACTIVO,INACTIVO,BORRADOR'],
            'requiere_coach' => ['nullable', 'boolean'],
            'renovacion_permitida' => ['nullable', 'boolean'],
            'renovacion_automatica' => ['nullable', 'boolean'],
            'popular' => ['nullable', 'boolean'],
            'destacado' => ['nullable', 'boolean'],
            'notas_admin' => ['nullable', 'string'],
        ]);

        if (($data['codigo'] ?? '') === '') {
            $data['codigo'] = null;
        }

        return $data + [
            'requiere_coach' => $request->boolean('requiere_coach', true),
            'renovacion_permitida' => $request->boolean('renovacion_permitida', true),
            'renovacion_automatica' => $request->boolean('renovacion_automatica'),
            'popular' => $request->boolean('popular'),
            'destacado' => $request->boolean('destacado'),
            'orden' => (int) $request->input('orden', 0),
            'dias_gracia' => (int) $request->input('dias_gracia', 3),
            'cupos_totales' => (int) $request->input('cupos_totales', 0),
        ];
    }

    private function parseFeatures(?string $raw): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $raw))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    private function modulosSync(array $ids): array
    {
        $sync = [];
        foreach (array_values($ids) as $i => $id) {
            $sync[(int) $id] = ['orden' => $i + 1];
        }

        return $sync;
    }
}
