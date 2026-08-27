<?php

namespace App\Services\Cliente;

use App\Models\Asistencia;
use App\Models\Cliente;
use App\Models\InvitacionWhatsapp;
use App\Models\MetaCliente;
use App\Models\Pago;
use App\Models\PlanEntrenamiento;
use App\Models\RachaCliente;
use App\Models\RegistroProgreso;
use App\Models\RegistroRutina;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Agrega datos del expediente presencial (CU-35 / RF asistencia-membresía).
 * Bundle: perfilpresencial01
 */
class PerfilPresencialService
{
    /**
     * @return array<string, mixed>
     */
    public function construirExpediente(Cliente $cliente): array
    {
        $cliente->loadMissing([
            'usuario',
            'coach.usuario',
            'horario',
            'preferencias',
            'accesos.modulo',
            'planesCliente.plan',
        ]);

        $planActivo = $cliente->planActivo();
        $planActivo?->loadMissing('plan');

        $inicioMes = now()->startOfMonth()->toDateString();
        $asistenciasMes = Asistencia::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('fecha', '>=', $inicioMes)
            ->whereIn('estado', ['PRESENTE', 'ASISTIO', 'OK'])
            ->count();

        $asistencias = Asistencia::query()
            ->with(['coach.usuario', 'sesion'])
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('fecha')
            ->limit(40)
            ->get();

        $serieAsistencia = Asistencia::query()
            ->selectRaw('fecha, COUNT(*) as total')
            ->where('id_cliente', $cliente->id_cliente)
            ->where('fecha', '>=', now()->subDays(28)->toDateString())
            ->whereIn('estado', ['PRESENTE', 'ASISTIO', 'OK'])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $hoy = now()->toDateString();
        $yaCheckinHoy = Asistencia::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('fecha', $hoy)
            ->whereIn('estado', ['PRESENTE', 'ASISTIO', 'OK'])
            ->exists();

        $pagos = Pago::query()
            ->with('plan')
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('creado_en')
            ->limit(20)
            ->get();

        $proximoPago = Pago::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('estado_pago', 'PENDIENTE')
            ->orderBy('creado_en')
            ->first();

        $progresos = RegistroProgreso::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('fecha_registro')
            ->limit(12)
            ->get();

        $seriePeso = RegistroProgreso::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->whereNotNull('peso_kg')
            ->orderBy('fecha_registro')
            ->limit(16)
            ->get(['fecha_registro', 'peso_kg', 'imc']);

        $metas = MetaCliente::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('id_meta')
            ->limit(6)
            ->get();

        $planEnt = PlanEntrenamiento::query()
            ->with(['rutinas'])
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('id_plan_entrenamiento')
            ->first();

        $rutinasSemana = RegistroRutina::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('completada', true)
            ->where('fecha_ejecucion', '>=', now()->subDays(7)->toDateString())
            ->count();

        $racha = RachaCliente::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('tipo', 'ASISTENCIA')
            ->first();

        $invitacionesWa = InvitacionWhatsapp::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('id_invitacion_wa')
            ->limit(5)
            ->get();

        $historialPlanes = $cliente->planesCliente()
            ->with('plan')
            ->orderByDesc('fecha_fin')
            ->limit(8)
            ->get();

        $accesos = $cliente->accesos()
            ->with('modulo')
            ->where('estado_acceso', 'ACTIVO')
            ->where('fecha_fin', '>=', now()->toDateString())
            ->get();

        $diasMes = max(1, (int) now()->day);
        $frecuencia = round($asistenciasMes / $diasMes, 2);

        return [
            'planActivo' => $planActivo,
            'asistenciasMes' => $asistenciasMes,
            'asistencias' => $asistencias,
            'serieAsistencia' => $serieAsistencia,
            'yaCheckinHoy' => $yaCheckinHoy,
            'pagos' => $pagos,
            'proximoPago' => $proximoPago,
            'progresos' => $progresos,
            'seriePeso' => $seriePeso,
            'metas' => $metas,
            'planEntrenamiento' => $planEnt,
            'rutinasSemana' => $rutinasSemana,
            'racha' => $racha,
            'invitacionesWa' => $invitacionesWa,
            'historialPlanes' => $historialPlanes,
            'accesos' => $accesos,
            'frecuencia' => $frecuencia,
            'kpis' => [
                'dias_plan' => $cliente->diasRestantesPlan(),
                'asistencias_mes' => $asistenciasMes,
                'racha' => (int) ($racha?->dias_consecutivos ?? $cliente->diasRacha()),
                'ultimo_peso' => $progresos->first()?->peso_kg,
                'pagos_pendientes' => $pagos->where('estado_pago', 'PENDIENTE')->count(),
            ],
        ];
    }

    public function registrarCheckin(Cliente $cliente, ?string $observacion = null): Asistencia
    {
        $hoy = Carbon::now()->toDateString();

        $existe = Asistencia::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('fecha', $hoy)
            ->whereIn('estado', ['PRESENTE', 'ASISTIO', 'OK'])
            ->first();

        if ($existe) {
            return $existe;
        }

        $coachId = $cliente->id_coach ?: $cliente->id_coach_asignado;
        if (! $coachId) {
            throw new \RuntimeException('No tienes coach asignado. Contacta a FigueFit para registrar asistencia.');
        }

        $asistencia = Asistencia::query()->create([
            'id_cliente' => $cliente->id_cliente,
            'id_coach' => $coachId,
            'id_sesion' => null,
            'fecha' => $hoy,
            'estado' => 'PRESENTE',
            'llama_racha' => true,
            'observacion' => $observacion,
            'registrada_en' => now(),
        ]);

        $this->actualizarRachaAsistencia($cliente, $hoy);

        return $asistencia;
    }

    private function actualizarRachaAsistencia(Cliente $cliente, string $fecha): void
    {
        $racha = RachaCliente::query()->firstOrNew([
            'id_cliente' => $cliente->id_cliente,
            'tipo' => 'ASISTENCIA',
        ]);

        $ayer = Carbon::parse($fecha)->subDay()->toDateString();
        $continua = $racha->ultima_fecha
            && Carbon::parse($racha->ultima_fecha)->toDateString() === $ayer;

        $dias = $continua ? ((int) $racha->dias_consecutivos + 1) : 1;
        $racha->dias_consecutivos = $dias;
        $racha->mejor_racha = max((int) $racha->mejor_racha, $dias);
        $racha->ultima_fecha = $fecha;
        $racha->actualizado_en = now();
        $racha->save();
    }

    /**
     * Calendario simple del mes actual: mapa fecha => bool.
     *
     * @return Collection<string, bool>
     */
    public function mapaAsistenciaMes(Cliente $cliente): Collection
    {
        $inicio = now()->startOfMonth()->toDateString();
        $fin = now()->endOfMonth()->toDateString();

        $fechas = Asistencia::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->whereBetween('fecha', [$inicio, $fin])
            ->whereIn('estado', ['PRESENTE', 'ASISTIO', 'OK'])
            ->pluck('fecha')
            ->map(fn ($f) => Carbon::parse($f)->toDateString())
            ->unique()
            ->flip()
            ->map(fn () => true);

        return $fechas;
    }
}
