<?php

namespace App\Http\Controllers\Institucional;

use App\Http\Controllers\Controller;
use App\Services\Institucional\InstitucionalHubService;
use App\Support\ResolvesInstitucionContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    use ResolvesInstitucionContext;

    public function __construct(private InstitucionalHubService $hub)
    {
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $institucion = $this->resolveInstitucionForUser($user);

        $query = $this->miembrosQuery($institucion);

        if ($buscar = trim((string) $request->query('q'))) {
            $query->whereHas('usuario', function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%")
                    ->orWhere('correo', 'like', "%{$buscar}%")
                    ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        if ($estado = $request->query('estado')) {
            $query->where('estado_cliente', $estado);
        }

        $miembros = $query->orderByDesc('actualizado_en')->paginate(12)->withQueryString();

        return view('institucional.usuarios.index', compact('user', 'institucion', 'miembros'));
    }

    public function show(int $id): View
    {
        $user = auth()->user();
        $institucion = $this->resolveInstitucionForUser($user);
        $miembro = $this->miembroOrAbort($institucion, $id);
        $miembro->load(['usuario', 'planesCliente.plan', 'rachas']);
        $progresos = $this->hub->actividadMiembro($miembro->id_cliente);
        $ultimoAcceso = $miembro->usuario?->ultimo_acceso_en;

        return view('institucional.usuarios.show', compact(
            'user',
            'institucion',
            'miembro',
            'progresos',
            'ultimoAcceso'
        ));
    }
}
