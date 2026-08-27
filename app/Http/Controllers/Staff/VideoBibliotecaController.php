<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CategoriaVideo;
use App\Models\PropuestaPlan;
use App\Models\Video;
use App\Support\ResolvesStaffContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VideoBibliotecaController extends Controller
{
    use ResolvesStaffContext;

    public function index(): View
    {
        $user = Auth::user();
        $coach = $this->resolveCoachForStaff($user);

        $categorias = CategoriaVideo::query()
            ->with(['videos' => fn ($q) => $q->where('estado', 'ACTIVO')->orderBy('dia_orden')])
            ->orderBy('nombre')
            ->get();

        $propuestasConVideo = PropuestaPlan::query()
            ->where('id_coach', $coach->id_coach)
            ->whereIn('estado', [
                PropuestaPlan::ESTADO_BORRADOR_ENTRENAMIENTO,
                PropuestaPlan::ESTADO_BORRADOR_NUTRICION,
            ])
            ->orderByDesc('id_propuesta')
            ->limit(6)
            ->get();

        return view('staff.videos.index', [
            'user' => $user,
            'coach' => $coach,
            'categorias' => $categorias,
            'totalVideos' => Video::query()->where('estado', 'ACTIVO')->count(),
            'propuestasConVideo' => $propuestasConVideo,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Auth::user();
        $this->resolveCoachForStaff(Auth::user());

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

        return back()->with('success', 'Referencia de video agregada a la biblioteca.');
    }
}
