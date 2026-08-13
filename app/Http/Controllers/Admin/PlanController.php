<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuloServicio;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $planes = Plan::with('modulos')->orderBy('orden')->get();

        return view('admin.planes.index', compact('planes'));
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
        $plan->update($data);
        $plan->modulos()->sync($this->modulosSync($request->input('modulos', [])));

        return redirect()->route('admin.planes.index')->with('success', 'Plan actualizado.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'moneda' => ['required', 'string', 'size:3'],
            'duracion_dias' => ['required', 'integer', 'min:1'],
            'modalidad' => ['required', 'in:VIRTUAL,PRESENCIAL,MIXTO'],
            'tipo_cliente' => ['required', 'in:INDIVIDUAL,INSTITUCIONAL,AMBOS'],
            'cupo_maximo' => ['nullable', 'integer', 'min:1'],
            'orden' => ['nullable', 'integer'],
            'etiqueta' => ['nullable', 'string', 'max:50'],
            'estado_plan' => ['required', 'in:ACTIVO,INACTIVO,BORRADOR'],
            'popular' => ['nullable', 'boolean'],
        ]) + [
            'popular' => $request->boolean('popular'),
            'orden' => (int) $request->input('orden', 0),
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
