<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Support\ResolvesStaffContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PerfilController extends Controller
{
    use ResolvesStaffContext;

    public function show(): View
    {
        $user = Auth::user();
        $coach = $this->resolveCoachForStaff($user);
        $hub = app(\App\Services\Staff\StaffHubService::class);

        return view('staff.perfil.show', [
            'user' => $user,
            'coach' => $coach,
            'coaches' => $hub->coachesActivos(),
            'totalClientes' => $hub->clientesDestacados($coach, 100)->count(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'nombres' => ['nullable', 'string', 'max:120'],
            'apellidos' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'especialidad' => ['nullable', 'string', 'max:120'],
        ]);

        $user->update(array_filter([
            'nombres' => $data['nombres'] ?? null,
            'apellidos' => $data['apellidos'] ?? null,
            'telefono' => $data['telefono'] ?? null,
        ], fn ($v) => $v !== null));

        session(['staff_especialidad' => $data['especialidad'] ?? session('staff_especialidad')]);

        return back()->with('success', 'Perfil operativo actualizado.');
    }
}
