<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Coach;
use App\Models\EnlaceRegistroInstitucional;
use App\Models\Institucion;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstitucionController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $instituciones = Institucion::query()
            ->with([
                'clientes.usuario',
                'clientes.planesCliente.plan',
                'clientes.rachas',
                'enlaces.plan',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('nombre', 'like', "%{$q}%")
                        ->orWhereHas('clientes.usuario', function ($u) use ($q) {
                            $u->where('nombres', 'like', "%{$q}%")
                                ->orWhere('apellidos', 'like', "%{$q}%");
                        });
                });
            })
            ->orderBy('nombre')
            ->get();

        $planes = Plan::query()->orderBy('nombre')->get(['id_plan', 'nombre', 'modalidad']);
        $clientesSinInst = Cliente::query()
            ->with('usuario')
            ->whereNull('id_institucion')
            ->orderByDesc('creado_en')
            ->limit(50)
            ->get();

        $kpis = [
            'total' => $instituciones->count(),
            'activas' => $instituciones->where('estado', 'ACTIVA')->count(),
            'cupos_usados' => (int) $instituciones->sum('cupos_usados'),
            'cupos_totales' => (int) $instituciones->sum('cupos_totales'),
            'enlaces' => $instituciones->sum(fn ($i) => $i->enlaces->count()),
            'miembros' => $instituciones->sum(fn ($i) => $i->clientes->count()),
        ];

        return view('admin.instituciones.index', compact('instituciones', 'q', 'planes', 'clientesSinInst', 'kpis'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'nit' => ['nullable', 'string', 'max:30', 'unique:instituciones,nit'],
            'contacto_nombre' => ['nullable', 'string', 'max:120'],
            'correo' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'ciudad' => ['nullable', 'string', 'max:80'],
            'cupos_totales' => ['nullable', 'integer', 'min:0'],
            'convenio' => ['nullable', 'string', 'max:200'],
        ]);

        Institucion::create([
            ...$data,
            'estado' => 'ACTIVA',
            'cupos_totales' => $data['cupos_totales'] ?? 0,
            'cupos_usados' => 0,
        ]);

        return back()->with('success', 'Institución creada.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $institucion = Institucion::query()->findOrFail($id);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'nit' => ['nullable', 'string', 'max:30', 'unique:instituciones,nit,'.$id.',id_institucion'],
            'contacto_nombre' => ['nullable', 'string', 'max:120'],
            'correo' => ['nullable', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'ciudad' => ['nullable', 'string', 'max:80'],
            'cupos_totales' => ['nullable', 'integer', 'min:0'],
            'convenio' => ['nullable', 'string', 'max:200'],
            'estado' => ['required', 'in:ACTIVA,INACTIVA,VENCIDA'],
        ]);

        $institucion->update($data);

        return back()->with('success', 'Institución actualizada.');
    }

    public function vincularCliente(Request $request, int $id): RedirectResponse
    {
        $institucion = Institucion::query()->findOrFail($id);

        $data = $request->validate([
            'id_cliente' => ['required', 'integer', 'exists:clientes,id_cliente'],
        ]);

        $cliente = Cliente::query()->findOrFail($data['id_cliente']);

        if ((int) $cliente->id_institucion === (int) $institucion->id_institucion) {
            return back()->with('success', 'El cliente ya está vinculado a esta institución.');
        }

        if ((int) $institucion->cupos_totales > 0
            && (int) $institucion->cupos_usados >= (int) $institucion->cupos_totales) {
            return back()->withErrors(['id_cliente' => 'La institución no tiene cupos disponibles.']);
        }

        $cliente->update([
            'id_institucion' => $institucion->id_institucion,
            'tipo_cliente' => 'INSTITUCIONAL',
        ]);

        $institucion->increment('cupos_usados');

        return back()->with('success', 'Cliente vinculado a la institución.');
    }

    public function storeEnlace(Request $request, int $id): RedirectResponse
    {
        $institucion = Institucion::query()->findOrFail($id);

        $data = $request->validate([
            'id_plan' => ['required', 'integer', 'exists:planes,id_plan'],
            'cupos_maximos' => ['required', 'integer', 'min:1'],
            'fecha_expiracion' => ['required', 'date', 'after:now'],
        ]);

        EnlaceRegistroInstitucional::create([
            'id_institucion' => $institucion->id_institucion,
            'id_plan' => $data['id_plan'],
            'token' => Str::lower(Str::random(40)),
            'cupos_maximos' => $data['cupos_maximos'],
            'usos_actuales' => 0,
            'fecha_expiracion' => $data['fecha_expiracion'],
            'estado' => 'ACTIVO',
            'creado_por' => Auth::id(),
        ]);

        return back()->with('success', 'Enlace institucional generado.');
    }
}
