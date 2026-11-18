<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NovedadWelcome;
use App\Models\Plan;
use App\Support\ResolvesAdminMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NovedadController extends Controller
{
    use ResolvesAdminMedia;

    public function index(): View
    {
        $novedades = NovedadWelcome::query()
            ->with('plan')
            ->orderBy('orden')
            ->orderByDesc('id_novedad')
            ->get();

        return view('admin.novedades.index', compact('novedades'));
    }

    public function create(): View
    {
        return view('admin.novedades.form', [
            'novedad' => new NovedadWelcome([
                'tipo' => 'GENERAL',
                'boton_texto' => 'Ver plan',
                'activo' => true,
                'orden' => 0,
                'url_destino' => '#planes',
            ]),
            'planes' => Plan::activos()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['imagen_url'] = $this->resolveMedia($request, 'imagen', 'novedades', 'imagen_url');

        if (! $data['imagen_url']) {
            return back()->withErrors(['imagen' => 'La foto es obligatoria.'])->withInput();
        }

        $data['creado_por'] = Auth::id();
        $data['activo'] = $request->boolean('activo', true);
        $data['orden'] = (int) $request->input('orden', 0);

        NovedadWelcome::create($data);

        return redirect()->route('admin.novedades.index')->with('success', 'Novedad lista para el welcome.');
    }

    public function edit(NovedadWelcome $novedad): View
    {
        return view('admin.novedades.form', [
            'novedad' => $novedad,
            'planes' => Plan::activos()->get(),
        ]);
    }

    public function update(Request $request, NovedadWelcome $novedad): RedirectResponse
    {
        $data = $this->validated($request);
        $data['imagen_url'] = $this->resolveMedia($request, 'imagen', 'novedades', 'imagen_url', $novedad->imagen_url);

        if (! $data['imagen_url']) {
            return back()->withErrors(['imagen' => 'La foto es obligatoria.'])->withInput();
        }

        $data['activo'] = $request->boolean('activo', true);
        $data['orden'] = (int) $request->input('orden', 0);
        $data['actualizado_en'] = now();
        $novedad->update($data);

        return redirect()->route('admin.novedades.index')->with('success', 'Novedad actualizada.');
    }

    public function destroy(NovedadWelcome $novedad): RedirectResponse
    {
        $novedad->delete();

        return redirect()->route('admin.novedades.index')->with('success', 'Novedad eliminada.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'subtitulo' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', 'in:NUEVO_PLAN,DESCUENTO,ACTUALIZACION,GENERAL'],
            'boton_texto' => ['required', 'string', 'max:80'],
            'url_destino' => ['nullable', 'string', 'max:500'],
            'id_plan' => ['nullable', 'integer', 'exists:planes,id_plan'],
            'orden' => ['nullable', 'integer', 'min:0'],
            'activo' => ['nullable', 'boolean'],
            'inicia_en' => ['nullable', 'date'],
            'termina_en' => ['nullable', 'date', 'after_or_equal:inicia_en'],
            'imagen' => ['nullable', 'image', 'max:5120'],
            'imagen_url' => ['nullable', 'string', 'max:500'],
        ]);

        foreach (['inicia_en', 'termina_en', 'id_plan', 'subtitulo', 'descripcion', 'url_destino'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }
}
