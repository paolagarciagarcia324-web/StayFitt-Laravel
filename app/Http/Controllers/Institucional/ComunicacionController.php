<?php

namespace App\Http\Controllers\Institucional;

use App\Http\Controllers\Controller;
use App\Models\ComunicadoInstitucional;
use App\Services\Comunicacion\NotificacionService;
use App\Support\ResolvesInstitucionContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComunicacionController extends Controller
{
    use ResolvesInstitucionContext;

    public function __construct(private NotificacionService $notificaciones)
    {
    }

    public function index(): View
    {
        $user = auth()->user();
        $institucion = $this->resolveInstitucionForUser($user);

        $comunicados = ComunicadoInstitucional::query()
            ->where('id_institucion', $institucion->id_institucion)
            ->orderByDesc('id_comunicado')
            ->paginate(10);

        $miembrosActivos = $this->miembrosQuery($institucion)
            ->whereIn('estado_cliente', ['ACTIVO', 'ACTIVA'])
            ->count();

        return view('institucional.comunicacion.index', compact(
            'user',
            'institucion',
            'comunicados',
            'miembrosActivos'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $institucion = $this->resolveInstitucionForUser($user);
        $this->assertInstitucionOwnership($institucion, $user);

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:200'],
            'mensaje' => ['required', 'string', 'max:2000'],
            'canal' => ['required', 'in:IN_APP,WHATSAPP,COMUNIDAD'],
        ]);

        $destinatarios = $this->miembrosQuery($institucion)
            ->whereIn('estado_cliente', ['ACTIVO', 'ACTIVA'])
            ->with('usuario')
            ->get();

        $enviados = 0;
        foreach ($destinatarios as $cliente) {
            $miembro = $cliente->usuario;
            if (! $miembro) {
                continue;
            }

            $this->notificaciones->crear(
                $miembro,
                'COMUNICADO_INSTITUCIONAL',
                $data['titulo'],
                $data['mensaje'],
                'institucion',
                $institucion->id_institucion,
                $data['canal'] === 'WHATSAPP' ? 'WHATSAPP' : 'IN_APP'
            );
            $enviados++;
        }

        ComunicadoInstitucional::create([
            'id_institucion' => $institucion->id_institucion,
            'id_user_emisor' => $user->id_user,
            'titulo' => $data['titulo'],
            'mensaje' => $data['mensaje'],
            'canal' => $data['canal'],
            'destinatarios' => $enviados,
        ]);

        return back()->with('institucional_ok', "Comunicado enviado a {$enviados} miembros activos.");
    }
}
