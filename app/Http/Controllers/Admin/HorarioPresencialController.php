<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\GrupoWhatsapp;
use App\Models\HorarioPresencial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
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

        $gruposWhatsapp = GrupoWhatsapp::query()
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get(['id_grupo_wa', 'nombre', 'tipo', 'criterio', 'link_invitacion', 'estado']);

        return view('admin.horarios.index', compact('horarios', 'gruposWhatsapp'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedHorario($request);
        $grupoLink = $this->resolverLinkWhatsapp($data);

        $codigo = HorarioPresencial::codigoDesdeHora($data['hora_inicio']);
        $base = $codigo;
        $n = 1;
        while (HorarioPresencial::query()->where('codigo', $codigo)->exists()) {
            $codigo = $base.'-'.$n;
            $n++;
        }

        $orden = (int) (HorarioPresencial::query()->max('orden') ?? 0) + 1;
        $activo = $request->boolean('activo');

        HorarioPresencial::query()->create([
            'codigo' => $codigo,
            'nombre' => $data['nombre'],
            'dias' => $data['dias'],
            'hora_inicio' => Carbon::parse($data['hora_inicio'])->format('H:i:s'),
            'hora_fin' => Carbon::parse($data['hora_fin'])->format('H:i:s'),
            'cupo_maximo' => $data['cupo_maximo'],
            'cupo_ocupado' => 0,
            'link_whatsapp' => $grupoLink,
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
        $data = $this->validatedHorario($request);

        $horario->update([
            'nombre' => $data['nombre'],
            'dias' => $data['dias'],
            'hora_inicio' => Carbon::parse($data['hora_inicio'])->format('H:i:s'),
            'hora_fin' => Carbon::parse($data['hora_fin'])->format('H:i:s'),
            'cupo_maximo' => $data['cupo_maximo'],
            'link_whatsapp' => $this->resolverLinkWhatsapp($data),
            'activo' => $request->boolean('activo'),
        ]);

        return back()->with('horario_ok', [
            'accion' => 'actualizado',
            'activo' => $request->boolean('activo'),
        ]);
    }

    /**
     * @return array{nombre: string, dias: string, hora_inicio: string, hora_fin: string, cupo_maximo: int, id_grupo_wa: int}
     */
    private function validatedHorario(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'dias' => ['required', 'string', 'max:80'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'cupo_maximo' => ['required', 'integer', 'min:1', 'max:80'],
            'id_grupo_wa' => [
                'required',
                'integer',
                Rule::exists('grupos_whatsapp', 'id_grupo_wa')->where(fn ($q) => $q->where('estado', 'ACTIVO')),
            ],
            'activo' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * @param  array{id_grupo_wa: int}  $data
     */
    private function resolverLinkWhatsapp(array $data): string
    {
        $grupo = GrupoWhatsapp::query()
            ->where('id_grupo_wa', $data['id_grupo_wa'])
            ->where('estado', 'ACTIVO')
            ->firstOrFail();

        $link = trim((string) $grupo->link_invitacion);

        return $link !== '' ? $link : $grupo->nombre;
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
