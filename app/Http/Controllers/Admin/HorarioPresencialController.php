<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\HorarioPresencial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class HorarioPresencialController extends Controller
{
    public function index(): View
    {
        HorarioPresencial::asegurarFranjasSala();

        $horarios = HorarioPresencial::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        return view('admin.horarios.index', compact('horarios'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'dias' => ['required', 'string', 'max:80'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'cupo_maximo' => ['required', 'integer', 'min:1', 'max:80'],
            'link_whatsapp' => ['nullable', 'string', 'max:300'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $codigo = HorarioPresencial::codigoDesdeHora($data['hora_inicio']);
        $base = $codigo;
        $n = 1;
        while (HorarioPresencial::query()->where('codigo', $codigo)->exists()) {
            $codigo = $base.'-'.$n;
            $n++;
        }

        $inicio = Carbon::parse($data['hora_inicio']);
        $grupo = trim((string) ($data['link_whatsapp'] ?? ''));
        if ($grupo === '') {
            $grupo = 'FigueFit CLUB '.HorarioPresencial::formatoHora12($data['hora_inicio']);
        }

        $orden = (int) (HorarioPresencial::query()->max('orden') ?? 0) + 1;
        $activo = $request->boolean('activo');

        HorarioPresencial::query()->create([
            'codigo' => $codigo,
            'nombre' => $data['nombre'],
            'dias' => $data['dias'],
            'hora_inicio' => $inicio->format('H:i:s'),
            'hora_fin' => Carbon::parse($data['hora_fin'])->format('H:i:s'),
            'cupo_maximo' => $data['cupo_maximo'],
            'cupo_ocupado' => 0,
            'link_whatsapp' => $grupo,
            'activo' => $activo,
            'orden' => $orden,
        ]);

        return back()->with('horario_ok', [
            'accion' => 'creado',
            'activo' => $activo,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $horario = HorarioPresencial::query()->findOrFail($id);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'dias' => ['required', 'string', 'max:80'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'cupo_maximo' => ['required', 'integer', 'min:1', 'max:80'],
            'link_whatsapp' => ['nullable', 'string', 'max:300'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $horario->update([
            'nombre' => $data['nombre'],
            'dias' => $data['dias'],
            'hora_inicio' => Carbon::parse($data['hora_inicio'])->format('H:i:s'),
            'hora_fin' => Carbon::parse($data['hora_fin'])->format('H:i:s'),
            'cupo_maximo' => $data['cupo_maximo'],
            'link_whatsapp' => $data['link_whatsapp'] ?? $horario->link_whatsapp,
            'activo' => $request->boolean('activo'),
        ]);

        return back()->with('horario_ok', [
            'accion' => 'actualizado',
            'activo' => $request->boolean('activo'),
        ]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $horario = HorarioPresencial::query()->findOrFail($id);

        if (Cliente::query()->where('id_horario', $horario->id_horario)->exists()) {
            $horario->update(['activo' => false]);

            return back()->with('horario_ok', [
                'accion' => 'desactivado',
                'activo' => false,
            ]);
        }

        $horario->delete();

        return back()->with('horario_ok', [
            'accion' => 'eliminado',
            'activo' => false,
        ]);
    }
}
