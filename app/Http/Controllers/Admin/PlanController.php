<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuloServicio;
use App\Models\Plan;
use App\Support\ResolvesAdminMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlanController extends Controller
{
    use ResolvesAdminMedia;
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $planes = Plan::query()
            ->with('modulos')
            ->when($q !== '', fn ($query) => $query->where('nombre', 'like', "%{$q}%"))
            ->orderBy('orden')
            ->get();

        $modulos = ModuloServicio::orderBy('orden')->get();

        return view('admin.planes.index', compact('planes', 'modulos', 'q'));
    }

    public function create(): View
    {
        $modulos = ModuloServicio::orderBy('orden')->get();

        return view('admin.planes.form', ['plan' => new Plan, 'modulos' => $modulos]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['nombre']).'-'.Str::lower(Str::random(4));
        $data['caracteristicas_json'] = $this->parseFeatures($request->input('caracteristicas'));
        $data['creado_por'] = Auth::id();
        $data['cupos_reservados'] = 0;
        $data['cupos_ocupados'] = 0;
        $data['imagen_url'] = $this->resolveMedia($request, 'imagen', 'planes', 'imagen_url');

        $plan = Plan::create($data);
        $plan->modulos()->sync($this->modulosSync($request->input('modulos', [])));

        return redirect()->route('admin.planes.index')->with('success', 'Plan creado.');
    }

    public function edit(Plan $plan): View
    {
        $plan->load('modulos');
        $modulos = ModuloServicio::orderBy('orden')->get();

        return view('admin.planes.form', compact('plan', 'modulos'));
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $data = $this->validated($request);
        $data['caracteristicas_json'] = $this->parseFeatures($request->input('caracteristicas'));
        $data['imagen_url'] = $this->resolveMedia($request, 'imagen', 'planes', 'imagen_url', $plan->imagen_url);
        $plan->update($data);
        $plan->modulos()->sync($this->modulosSync($request->input('modulos', [])));

        return redirect()->route('admin.planes.index')->with('success', 'Plan actualizado.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'codigo' => ['nullable', 'string', 'max:40'],
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
        ]);

        return $data + [
            'requiere_coach' => $request->boolean('requiere_coach'),
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
