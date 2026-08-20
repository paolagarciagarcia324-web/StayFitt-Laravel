<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CalendarioController extends Controller
{
    public function index(): View
    {
        $mes = Carbon::now()->startOfMonth();
        $finMes = $mes->copy()->endOfMonth();
        $hasta = Carbon::now()->addMonths(2)->endOfMonth();

        $eventos = Evento::query()
            ->whereBetween('fecha_inicio', [$mes, $hasta])
            ->orderBy('fecha_inicio')
            ->get();

        $eventosPorDia = $eventos
            ->filter(fn ($e) => $e->fecha_inicio && $e->fecha_inicio->between($mes, $finMes))
            ->groupBy(fn ($e) => $e->fecha_inicio->format('Y-m-d'));

        $celdas = [];
        $inicioGrid = $mes->copy()->startOfWeek(Carbon::MONDAY);
        $finGrid = $finMes->copy()->endOfWeek(Carbon::SUNDAY);
        for ($d = $inicioGrid->copy(); $d->lte($finGrid); $d->addDay()) {
            $key = $d->toDateString();
            $celdas[] = [
                'fecha' => $d->copy(),
                'enMes' => $d->month === $mes->month,
                'hoy' => $d->isToday(),
                'eventos' => $eventosPorDia->get($key, collect()),
            ];
        }

        $sesiones = collect();
        if (Schema::hasTable('sesiones')) {
            $sesiones = DB::table('sesiones')
                ->whereBetween('fecha', [$mes->toDateString(), $hasta->toDateString()])
                ->orderBy('fecha')
                ->orderBy('hora_inicio')
                ->limit(40)
                ->get();
        }

        return view('admin.calendario.index', compact('eventos', 'sesiones', 'mes', 'celdas'));
    }
}
