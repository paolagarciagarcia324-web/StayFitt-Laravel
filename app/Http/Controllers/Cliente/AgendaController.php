<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\EventoParticipante;
use App\Support\ResolvesCliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AgendaController extends Controller
{
    use ResolvesCliente;

    public function index(): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $ahora = now();

        $eventos = Evento::query()
            ->where(function ($q) {
                $q->whereIn('estado', ['ACTIVO', 'PUBLICADO', 'ABIERTO'])->orWhereNull('estado');
            })
            ->where(function ($q) use ($ahora) {
                $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $ahora);
            })
            ->orderBy('fecha_inicio')
            ->limit(40)
            ->get();

        $misInscripciones = EventoParticipante::query()
            ->with('evento')
            ->where('id_cliente', $cliente->id_cliente)
            ->whereIn('estado', ['INSCRITO', 'CONFIRMADO', 'ACTIVO'])
            ->orderByDesc('inscrito_en')
            ->get()
            ->keyBy('id_evento');

        return view('cliente.agenda.index', compact('cliente', 'eventos', 'misInscripciones'));
    }

    public function inscribir(int $id): RedirectResponse
    {
        $cliente = $this->resolveClienteOrAbort();
        $evento = Evento::query()->findOrFail($id);

        $existe = EventoParticipante::query()
            ->where('id_evento', $evento->id_evento)
            ->where('id_cliente', $cliente->id_cliente)
            ->whereIn('estado', ['INSCRITO', 'CONFIRMADO', 'ACTIVO'])
            ->exists();

        if ($existe) {
            return back()->with('success', 'Ya estás inscrito en este evento.');
        }

        if ($evento->cupo_maximo && (int) $evento->cupo_inscritos >= (int) $evento->cupo_maximo) {
            return back()->with('error', 'Este evento ya no tiene cupos disponibles.');
        }

        EventoParticipante::query()->create([
            'id_evento' => $evento->id_evento,
            'id_cliente' => $cliente->id_cliente,
            'estado' => 'INSCRITO',
            'inscrito_en' => now(),
        ]);

        $evento->increment('cupo_inscritos');

        return back()->with('success', 'Inscripción confirmada.');
    }

    public function cancelar(int $id): RedirectResponse
    {
        $cliente = $this->resolveClienteOrAbort();
        $participacion = EventoParticipante::query()
            ->where('id_evento', $id)
            ->where('id_cliente', $cliente->id_cliente)
            ->whereIn('estado', ['INSCRITO', 'CONFIRMADO', 'ACTIVO'])
            ->first();

        if (! $participacion) {
            return back()->with('error', 'No tienes inscripción activa en este evento.');
        }

        $participacion->update(['estado' => 'CANCELADO']);
        Evento::query()->where('id_evento', $id)->where('cupo_inscritos', '>', 0)->decrement('cupo_inscritos');

        return back()->with('success', 'Inscripción cancelada.');
    }
}
