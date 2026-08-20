<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HorarioPresencial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HorarioPresencialController extends Controller
{
    public function index(): View
    {
        $horarios = HorarioPresencial::query()->orderBy('orden')->get();

        return view('admin.horarios.index', compact('horarios'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $horario = HorarioPresencial::query()->findOrFail($id);

        $data = $request->validate([
            'cupo_maximo' => ['required', 'integer', 'min:1', 'max:80'],
            'link_whatsapp' => ['nullable', 'string', 'max:500'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $horario->update([
            'cupo_maximo' => $data['cupo_maximo'],
            'link_whatsapp' => $data['link_whatsapp'] ?? $horario->link_whatsapp,
            'activo' => $request->boolean('activo'),
        ]);

        return back()->with('success', 'Horario actualizado.');
    }
}
