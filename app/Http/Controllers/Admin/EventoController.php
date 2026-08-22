<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Notificacion;
use App\Support\ResolvesAdminMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventoController extends Controller
{
    use ResolvesAdminMedia;

    public function index(): View
    {
        $eventos = Evento::query()->orderByDesc('fecha_inicio')->paginate(10)->withQueryString();

        return view('admin.eventos.index', compact('eventos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateEvento($request);

        $evento = Evento::create([
            ...collect($data)->except(['imagen'])->all(),
            'imagen_url' => $this->resolveMedia($request, 'imagen', 'eventos', 'imagen_url'),
            'id_admin' => Auth::id(),
            'cupo_inscritos' => 0,
        ]);

        $this->notifyAdminCalendario(
            'EVENTO',
            'Nuevo evento en calendario',
            "Se creó «{$evento->nombre}» para el ".optional($evento->fecha_inicio)->format('d/m/Y H:i').'.',
            (int) $evento->id_evento
        );

        return back()->with('evento_ok', ['accion' => 'creado']);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $evento = Evento::query()->findOrFail($id);

        $data = $this->validateEvento($request, $evento);

        $evento->update([
            ...collect($data)->except(['imagen'])->all(),
            'imagen_url' => $this->resolveMedia($request, 'imagen', 'eventos', 'imagen_url', $evento->imagen_url),
        ]);

        $this->notifyAdminCalendario(
            'EVENTO',
            'Evento actualizado',
            "Se actualizó «{$evento->nombre}» ({$evento->fecha_inicio?->format('d/m/Y H:i')}).",
            (int) $evento->id_evento
        );

        return back()->with('evento_ok', ['accion' => 'actualizado']);
    }

    public function destroy(int $id): RedirectResponse
    {
        $evento = Evento::query()->findOrFail($id);
        $nombre = $evento->nombre;
        $evento->delete();

        $this->notifyAdminCalendario(
            'EVENTO',
            'Evento eliminado',
            "Se eliminó «{$nombre}» del calendario.",
            $id
        );

        return back()->with('evento_ok', ['accion' => 'eliminado']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateEvento(Request $request, ?Evento $evento = null): array
    {
        $hasExistingCover = $evento && filled($evento->imagen_url);

        return $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['required', 'string'],
            'tipo' => ['required', 'string', 'max:30'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'modalidad' => ['required', 'in:VIRTUAL,PRESENCIAL,MIXTO'],
            'cupo_maximo' => ['required', 'integer', 'min:1'],
            'estado' => ['required', 'in:BORRADOR,PUBLICADO,CERRADO,CANCELADO'],
            'imagen_url' => [
                Rule::requiredIf(fn () => ! $request->hasFile('imagen') && ! $hasExistingCover),
                'nullable',
                'string',
                'max:500',
            ],
            'imagen' => [
                Rule::requiredIf(fn () => blank($request->input('imagen_url')) && ! $hasExistingCover),
                'nullable',
                'image',
                'max:4096',
            ],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior al inicio.',
            'cupo_maximo.required' => 'El cupo máximo es obligatorio.',
            'imagen.required' => 'Sube una foto o indica una URL de portada.',
            'imagen_url.required' => 'Sube una foto o indica una URL de portada.',
        ]);
    }

    private function notifyAdminCalendario(string $refTipo, string $titulo, string $mensaje, int $refId): void
    {
        if (! Schema::hasTable('notificaciones')) {
            return;
        }
        $adminId = Auth::id();
        if (! $adminId) {
            return;
        }
        Notificacion::create([
            'id_user' => $adminId,
            'tipo' => 'CALENDARIO',
            'titulo' => $titulo,
            'mensaje' => \Illuminate\Support\Str::limit($mensaje, 490),
            'leida' => false,
            'canal' => 'PANEL',
            'referencia_tipo' => $refTipo,
            'referencia_id' => $refId,
            'creado_en' => now(),
        ]);
    }
}
