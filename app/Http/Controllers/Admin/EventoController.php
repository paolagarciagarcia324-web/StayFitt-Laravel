<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EventoController extends Controller
{
    public function index(): View
    {
        $eventos = Evento::query()->orderByDesc('fecha_inicio')->paginate(20);

        return view('admin.eventos.index', compact('eventos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', 'string', 'max:30'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'modalidad' => ['required', 'in:VIRTUAL,PRESENCIAL,MIXTO'],
            'cupo_maximo' => ['nullable', 'integer', 'min:1'],
            'estado' => ['required', 'in:BORRADOR,PUBLICADO,CERRADO,CANCELADO'],
        ]);

        Evento::create([
            ...$data,
            'id_admin' => Auth::id(),
            'cupo_inscritos' => 0,
        ]);

        return back()->with('success', 'Evento creado.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $evento = Evento::query()->findOrFail($id);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', 'string', 'max:30'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'modalidad' => ['required', 'in:VIRTUAL,PRESENCIAL,MIXTO'],
            'cupo_maximo' => ['nullable', 'integer', 'min:1'],
            'estado' => ['required', 'in:BORRADOR,PUBLICADO,CERRADO,CANCELADO'],
        ]);

        $evento->update($data);

        return back()->with('success', 'Evento actualizado.');
    }
}
