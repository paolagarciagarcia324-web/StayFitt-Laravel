<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\CategoriaVideo;
use App\Models\PropuestaPlan;
use App\Models\Video;
use App\Support\ResolvesAdminMedia;
use App\Support\ResolvesStaffCoach;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VideoClaseController extends Controller
{
    use ResolvesAdminMedia;
    use ResolvesStaffCoach;

    public function store(Request $request, PropuestaPlan $propuesta): RedirectResponse
    {
        $this->guardEditable($propuesta);

        $categoria = $this->categoriaPrincipal($propuesta);
        $nextDia = (int) Video::query()
            ->where('id_categoria_video', $categoria->id_categoria_video)
            ->max('dia_orden') + 1;

        $data = $this->validated($request, $categoria->id_categoria_video, $nextDia);

        Video::create([
            'id_categoria_video' => $categoria->id_categoria_video,
            'dia_orden' => $data['dia_orden'],
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'url_video' => $data['url_video'],
            'duracion_segundos' => $data['duracion_segundos'] ?? null,
            'requiere_ia' => false,
            'estado' => 'ACTIVO',
        ]);

        return back()->with('success', 'Clase del día '.$data['dia_orden'].' agregada.');
    }

    public function update(Request $request, PropuestaPlan $propuesta, int $id): RedirectResponse
    {
        $this->guardEditable($propuesta);
        $video = $this->findVideo($propuesta, $id);
        $data = $this->validated($request, $video->id_categoria_video, $video->dia_orden, $video);

        $video->update([
            'dia_orden' => $data['dia_orden'],
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'url_video' => $data['url_video'],
            'duracion_segundos' => $data['duracion_segundos'] ?? null,
        ]);

        return back()->with('success', 'Clase actualizada.');
    }

    public function destroy(PropuestaPlan $propuesta, int $id): RedirectResponse
    {
        $this->guardEditable($propuesta);
        $video = $this->findVideo($propuesta, $id);
        $video->delete();

        return back()->with('success', 'Clase eliminada.');
    }

    private function validated(Request $request, int $categoriaId, int $defaultDia, ?Video $video = null): array
    {
        $tipo = $request->input('tipo_fuente', 'URL');

        $rules = [
            'tipo_fuente' => ['required', 'in:URL,ARCHIVO'],
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'dia_orden' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('videos', 'dia_orden')
                    ->where(fn ($q) => $q->where('id_categoria_video', $categoriaId))
                    ->ignore($video?->id_video, 'id_video'),
            ],
            'duracion_segundos' => ['nullable', 'integer', 'min:1'],
            'url_video' => ['nullable', 'string', 'max:500'],
            'archivo_video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:204800'],
        ];

        $data = $request->validate($rules);
        $data['dia_orden'] = (int) ($data['dia_orden'] ?? $defaultDia);

        if ($tipo === 'ARCHIVO') {
            if (! $request->hasFile('archivo_video') && ! $video?->url_video) {
                throw ValidationException::withMessages([
                    'archivo_video' => 'Debes subir un archivo de video.',
                ]);
            }
            $resolved = $this->resolveMedia($request, 'archivo_video', 'planes/videos', null, $video?->url_video);
            $data['url_video'] = $resolved;
        } else {
            $url = trim((string) ($data['url_video'] ?? ''));
            if ($url === '' && ! $video?->url_video) {
                throw ValidationException::withMessages([
                    'url_video' => 'Debes indicar la URL del video.',
                ]);
            }
            $data['url_video'] = $url !== '' ? $url : $video->url_video;
        }

        return $data;
    }

    private function guardEditable(PropuestaPlan $propuesta): void
    {
        if (! $this->userCanAccessPropuesta(Auth::user(), $propuesta)) {
            abort(403);
        }

        if (! $propuesta->esEditable() && $propuesta->estado !== PropuestaPlan::ESTADO_BORRADOR_ENTRENAMIENTO) {
            if (! in_array($propuesta->estado, [
                PropuestaPlan::ESTADO_BORRADOR_ENTRENAMIENTO,
                PropuestaPlan::ESTADO_BORRADOR_NUTRICION,
                PropuestaPlan::ESTADO_RECHAZADO,
            ], true)) {
                abort(403, 'La propuesta no es editable.');
            }
        }

        if (! $propuesta->esEditable()) {
            abort(403, 'La propuesta no es editable.');
        }
    }

    private function categoriaPrincipal(PropuestaPlan $propuesta): CategoriaVideo
    {
        $propuesta->loadMissing('programa.categorias');
        $categoria = $propuesta->programa?->categorias->first();

        if (! $categoria) {
            $categoria = CategoriaVideo::create([
                'id_programa' => $propuesta->id_programa,
                'nombre' => 'Clases diarias',
                'orden' => 1,
            ]);
        }

        return $categoria;
    }

    private function findVideo(PropuestaPlan $propuesta, int $id): Video
    {
        $ids = CategoriaVideo::query()
            ->where('id_programa', $propuesta->id_programa)
            ->pluck('id_categoria_video');

        return Video::query()
            ->whereIn('id_categoria_video', $ids)
            ->where('id_video', $id)
            ->firstOrFail();
    }
}
