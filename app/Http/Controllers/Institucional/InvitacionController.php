<?php

namespace App\Http\Controllers\Institucional;

use App\Http\Controllers\Controller;
use App\Models\EnlaceRegistroInstitucional;
use App\Models\InvitacionInstitucional;
use App\Services\Institucional\InstitucionalHubService;
use App\Services\Institucional\InstitucionalRegistroService;
use App\Support\ResolvesInstitucionContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvitacionController extends Controller
{
    use ResolvesInstitucionContext;

    public function __construct(
        private InstitucionalHubService $hub,
        private InstitucionalRegistroService $registro
    ) {
    }

    public function index(): View
    {
        $user = auth()->user();
        $institucion = $this->resolveInstitucionForUser($user);
        $institucion->load('planContratado');

        $invitaciones = InvitacionInstitucional::query()
            ->where('id_institucion', $institucion->id_institucion)
            ->orderByDesc('id_invitacion')
            ->paginate(10);

        $invitaciones->getCollection()->each->marcarExpiradaSiCorresponde();

        $enlaces = EnlaceRegistroInstitucional::query()
            ->with('plan')
            ->where('id_institucion', $institucion->id_institucion)
            ->orderByDesc('id_enlace')
            ->get()
            ->map(function ($e) {
                return [
                    'model' => $e,
                    'estado' => $this->hub->estadoEnlace($e),
                    'url' => route('registro-institucional.enlace', $e->token),
                ];
            });

        $planes = $this->hub->planesDisponibles();

        return view('institucional.invitaciones.index', compact(
            'user',
            'institucion',
            'invitaciones',
            'enlaces',
            'planes'
        ));
    }

    public function storeInvitacion(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $institucion = $this->resolveInstitucionForUser($user);
        $this->assertInstitucionOwnership($institucion, $user);

        if ($institucion->cupos_totales > 0 && $institucion->cupos_usados >= $institucion->cupos_totales) {
            return back()->withErrors(['correo' => 'No hay cupos disponibles en el convenio.']);
        }

        $data = $request->validate([
            'correo' => ['required', 'email', 'max:150'],
            'nombre_sugerido' => ['nullable', 'string', 'max:120'],
            'id_plan' => ['nullable', 'integer', 'exists:planes,id_plan'],
            'dias_validez' => ['required', 'integer', 'min:1', 'max:90'],
        ]);

        InvitacionInstitucional::create([
            'id_institucion' => $institucion->id_institucion,
            'id_plan' => $data['id_plan'] ?? $institucion->id_plan_contratado,
            'correo' => strtolower($data['correo']),
            'nombre_sugerido' => $data['nombre_sugerido'] ?? null,
            'token' => $this->registro->generarTokenInvitacion(),
            'estado' => 'PENDIENTE',
            'fecha_expiracion' => now()->addDays((int) $data['dias_validez']),
            'id_user_creador' => $user->id_user,
        ]);

        return back()->with('institucional_ok', 'Invitación creada. Comparte el enlace con el colaborador.');
    }

    public function storeEnlace(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $institucion = $this->resolveInstitucionForUser($user);
        $this->assertInstitucionOwnership($institucion, $user);

        $data = $request->validate([
            'id_plan' => ['required', 'integer', 'exists:planes,id_plan'],
            'cupos_maximos' => ['required', 'integer', 'min:1', 'max:500'],
            'dias_validez' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        EnlaceRegistroInstitucional::create([
            'id_institucion' => $institucion->id_institucion,
            'id_plan' => $data['id_plan'],
            'token' => Str::lower(Str::random(40)),
            'cupos_maximos' => $data['cupos_maximos'],
            'usos_actuales' => 0,
            'fecha_expiracion' => now()->addDays((int) $data['dias_validez']),
            'estado' => 'ACTIVO',
            'creado_por' => $user->id_user,
        ]);

        return back()->with('institucional_ok', 'Enlace grupal generado correctamente.');
    }

    public function destroyInvitacion(int $id): RedirectResponse
    {
        $user = auth()->user();
        $institucion = $this->resolveInstitucionForUser($user);

        $inv = InvitacionInstitucional::query()
            ->where('id_institucion', $institucion->id_institucion)
            ->where('id_invitacion', $id)
            ->firstOrFail();

        if ($inv->estado === 'ACEPTADA') {
            return back()->withErrors(['invitacion' => 'No se puede eliminar una invitación ya aceptada.']);
        }

        $inv->delete();

        return back()->with('institucional_ok', 'Invitación eliminada.');
    }
}
