<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\PropuestaPlan;
use App\Support\ResolvesStaffCoach;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesStaffCoach;

    public function index(): View
    {
        $user = Auth::user();

        $base = PropuestaPlan::query()
            ->when(
                $user->tieneRol('COACH') && ! $user->tieneRol('ASISTENTE_TECNICO') && ! $user->tieneRol('ADMIN'),
                function ($query) use ($user) {
                    $query->where('id_coach', $user->coach?->id_coach ?: 0);
                }
            );

        $stats = [
            'borradores' => (clone $base)->whereIn('estado', [
                PropuestaPlan::ESTADO_BORRADOR_ENTRENAMIENTO,
                PropuestaPlan::ESTADO_BORRADOR_NUTRICION,
            ])->count(),
            'pendientes' => (clone $base)->where('estado', PropuestaPlan::ESTADO_PENDIENTE_REVISION)->count(),
            'publicados' => (clone $base)->where('estado', PropuestaPlan::ESTADO_PUBLICADO)->count(),
            'rechazados' => (clone $base)->where('estado', PropuestaPlan::ESTADO_RECHAZADO)->count(),
        ];

        $recientes = (clone $base)->orderByDesc('id_propuesta')->limit(6)->get();

        return view('coach.dashboard', compact('stats', 'recientes'));
    }
}
