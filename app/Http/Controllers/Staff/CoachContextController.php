<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachContextController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user && ($user->tieneRol('ASISTENTE_TECNICO') || $user->tieneRol('ADMIN')), 403);

        $data = $request->validate([
            'id_coach' => ['required', 'integer', 'exists:coaches,id_coach'],
        ]);

        $coach = Coach::query()->findOrFail($data['id_coach']);
        session(['staff_coach_id' => $coach->id_coach]);

        return back()->with('success', 'Coach operativo: '.trim($coach->usuario?->nombres ?? 'Coach #'.$coach->id_coach));
    }
}
