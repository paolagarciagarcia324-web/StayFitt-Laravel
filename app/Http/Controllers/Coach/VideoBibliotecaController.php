<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\CategoriaVideo;
use App\Models\Video;
use App\Support\ResolvesStaffCoach;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoBibliotecaController extends Controller
{
    use ResolvesStaffCoach;

    public function index(): View
    {
        $coach = $this->resolveCoachOrAbort();

        $categorias = CategoriaVideo::query()
            ->with(['videos' => fn ($q) => $q->where('estado', 'ACTIVO')->orderBy('dia_orden')])
            ->orderBy('nombre')
            ->get();

        $totalVideos = Video::query()->where('estado', 'ACTIVO')->count();

        return view('coach.videos.index', compact('coach', 'categorias', 'totalVideos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->resolveCoachOrAbort();

        $data = $request->validate([
            'id_categoria_video' => ['required', 'integer', 'exists:categorias_video,id_categoria_video'],
            'titulo' => ['required', 'string', 'max:160'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'url_video' => ['required', 'url', 'max:500'],
            'duracion_segundos' => ['nullable', 'integer', 'min:30', 'max:7200'],
        ]);

        $nextDia = (int) Video::query()
            ->where('id_categoria_video', $data['id_categoria_video'])
            ->max('dia_orden') + 1;

        Video::create([
            ...$data,
            'dia_orden' => $nextDia,
            'requiere_ia' => false,
            'estado' => 'ACTIVO',
        ]);

        return back()->with('success', 'Video agregado a la biblioteca.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->resolveCoachOrAbort();

        $video = Video::query()->findOrFail($id);

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:160'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'url_video' => ['required', 'url', 'max:500'],
            'duracion_segundos' => ['nullable', 'integer', 'min:30', 'max:7200'],
            'estado' => ['nullable', 'string', 'in:ACTIVO,INACTIVO'],
        ]);

        $video->update($data);

        return back()->with('success', 'Video actualizado.');
    }
}
