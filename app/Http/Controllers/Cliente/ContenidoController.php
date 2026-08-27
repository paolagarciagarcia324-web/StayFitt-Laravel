<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\ProgresoVideo;
use App\Models\Video;
use App\Support\ResolvesCliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContenidoController extends Controller
{
    use ResolvesCliente;

    public function index(): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $videos = Video::query()
            ->with('categoriaVideo')
            ->where(function ($q) {
                $q->where('estado', 'ACTIVO')->orWhereNull('estado');
            })
            ->orderBy('dia_orden')
            ->orderBy('id_video')
            ->get();

        $progresos = ProgresoVideo::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->get()
            ->keyBy('id_video');

        $completados = $progresos->where('estado', 'COMPLETADO')->count();

        return view('cliente.contenido.index', compact('cliente', 'videos', 'progresos', 'completados'));
    }

    public function show(int $id): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $video = Video::query()->with('categoriaVideo')->findOrFail($id);
        $progreso = ProgresoVideo::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('id_video', $video->id_video)
            ->first();

        if (! $progreso) {
            $progreso = ProgresoVideo::query()->create([
                'id_cliente' => $cliente->id_cliente,
                'id_video' => $video->id_video,
                'id_plan_cliente' => $cliente->planActivo()?->id_plan_cliente,
                'estado' => 'EN_PROGRESO',
                'iniciado_en' => now(),
                'porcentaje' => 0,
                'llama_racha' => false,
            ]);
        } elseif ($progreso->estado === 'PENDIENTE' || ! $progreso->iniciado_en) {
            $progreso->update([
                'estado' => 'EN_PROGRESO',
                'iniciado_en' => $progreso->iniciado_en ?? now(),
            ]);
        }

        return view('cliente.contenido.show', compact('cliente', 'video', 'progreso'));
    }

    public function completar(int $id): RedirectResponse
    {
        $cliente = $this->resolveClienteOrAbort();
        $video = Video::query()->findOrFail($id);

        ProgresoVideo::query()->updateOrCreate(
            [
                'id_cliente' => $cliente->id_cliente,
                'id_video' => $video->id_video,
            ],
            [
                'id_plan_cliente' => $cliente->planActivo()?->id_plan_cliente,
                'estado' => 'COMPLETADO',
                'iniciado_en' => now(),
                'completado_en' => now(),
                'porcentaje' => 100,
                'llama_racha' => true,
            ]
        );

        return back()->with('success', 'Video marcado como completado.');
    }
}
