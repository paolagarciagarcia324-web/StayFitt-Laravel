<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminPagoUpdateRequest;
use App\Models\Pago;
use App\Services\ComprobantePasarelaService;
use App\Services\SolicitudAprobacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PagoController extends Controller
{
    public function __construct(
        private ComprobantePasarelaService $comprobantes,
        private SolicitudAprobacionService $aprobacion,
    ) {
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $mesRaw = trim((string) $request->get('mes', now()->format('Y-m')));
        $mes = preg_match('/^\d{4}-\d{2}$/', $mesRaw) ? $mesRaw : now()->format('Y-m');
        $mesInicio = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
        $mesFin = $mesInicio->copy()->endOfMonth();
        $diaRaw = trim((string) $request->get('dia', ''));
        $dia = preg_match('/^\d{4}-\d{2}-\d{2}$/', $diaRaw) ? $diaRaw : null;
        if ($dia && ! str_starts_with($dia, $mes)) {
            $dia = null;
        }

        $scopeMes = function ($query) use ($mesInicio, $mesFin) {
            $query->where(function ($inner) use ($mesInicio, $mesFin) {
                $inner->whereBetween('fecha_pago', [$mesInicio, $mesFin])
                    ->orWhere(function ($nullFecha) use ($mesInicio, $mesFin) {
                        $nullFecha->whereNull('fecha_pago')
                            ->whereBetween('creado_en', [$mesInicio, $mesFin]);
                    });
            });
        };

        $pagos = Pago::query()
            ->with(['solicitud', 'plan', 'cliente.usuario', 'planCliente.cliente.usuario'])
            ->tap($scopeMes)
            ->when($dia, function ($query) use ($dia) {
                $dayStart = Carbon::createFromFormat('Y-m-d', $dia)->startOfDay();
                $dayEnd = $dayStart->copy()->endOfDay();
                $query->where(function ($inner) use ($dayStart, $dayEnd) {
                    $inner->whereBetween('fecha_pago', [$dayStart, $dayEnd])
                        ->orWhere(function ($nullFecha) use ($dayStart, $dayEnd) {
                            $nullFecha->whereNull('fecha_pago')
                                ->whereBetween('creado_en', [$dayStart, $dayEnd]);
                        });
                });
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('referencia', 'like', "%{$q}%")
                        ->orWhere('referencia_pago', 'like', "%{$q}%")
                        ->orWhereHas('solicitud', function ($s) use ($q) {
                            $s->where('correo', 'like', "%{$q}%")
                                ->orWhere('codigo_solicitud', 'like', "%{$q}%")
                                ->orWhere('nombres', 'like', "%{$q}%")
                                ->orWhere('apellidos', 'like', "%{$q}%");
                        })
                        ->orWhereHas('cliente.usuario', function ($u) use ($q) {
                            $u->where('nombres', 'like', "%{$q}%")
                                ->orWhere('apellidos', 'like', "%{$q}%")
                                ->orWhere('correo', 'like', "%{$q}%");
                        });
                });
            })
            ->orderByDesc('fecha_pago')
            ->orderByDesc('creado_en')
            ->paginate(10)
            ->withQueryString();

        foreach ($pagos as $pago) {
            $pas = strtoupper((string) ($pago->pasarela ?: ''));
            $met = strtoupper((string) ($pago->metodo_pago ?: $pago->metodo ?: ''));
            $esPasarela = ($pas !== '' && ! in_array($pas, ['NINGUNA', 'PENDIENTE'], true)) || $met === 'PASARELA';
            if ($esPasarela) {
                $this->comprobantes->asegurar($pago, $pago->solicitud, $pas !== '' ? $pas : 'PASARELA');
            }
        }

        $baseMes = Pago::query()->tap($scopeMes);

        $total = (clone $baseMes)->count();
        $validados = (clone $baseMes)->where('estado_pago', 'VALIDADO')->count();
        $pendientes = (clone $baseMes)->where('estado_pago', 'PENDIENTE')->count();
        $rechazados = (clone $baseMes)->whereIn('estado_pago', ['RECHAZADO', 'CANCELADO', 'ANULADO'])->count();

        $resumen = [
            'total' => $total,
            'validados' => $validados,
            'pendientes' => $pendientes,
            'otros' => max($rechazados, max(0, $total - $validados - $pendientes)),
            'monto_mes' => (float) (clone $baseMes)
                ->where('estado_pago', 'VALIDADO')
                ->sum('monto'),
        ];

        $conteoDias = [];
        foreach ((clone $baseMes)->get(['fecha_pago', 'creado_en']) as $pagoDia) {
            $fecha = $pagoDia->fecha_pago ?? $pagoDia->creado_en;
            if (! $fecha) {
                continue;
            }
            $key = Carbon::parse($fecha)->format('Y-m-d');
            $conteoDias[$key] = ($conteoDias[$key] ?? 0) + 1;
        }

        $calInicio = $mesInicio->copy()->startOfWeek(Carbon::MONDAY);
        $calFin = $mesFin->copy()->endOfWeek(Carbon::SUNDAY);
        $calendario = [];
        for ($cursor = $calInicio->copy(); $cursor->lte($calFin); $cursor->addDay()) {
            $key = $cursor->format('Y-m-d');
            $calendario[] = [
                'fecha' => $key,
                'dia' => $cursor->day,
                'enMes' => $cursor->month === $mesInicio->month,
                'hoy' => $cursor->isToday(),
                'count' => (int) ($conteoDias[$key] ?? 0),
                'activo' => $dia === $key,
            ];
        }

        $meses = collect(range(0, 11))->map(function (int $i) {
            $d = now()->startOfMonth()->subMonths($i);

            return [
                'value' => $d->format('Y-m'),
                'label' => ucfirst($d->locale('es')->translatedFormat('F Y')),
            ];
        });

        $mesAnterior = $mesInicio->copy()->subMonth()->format('Y-m');
        $mesSiguiente = $mesInicio->copy()->addMonth()->format('Y-m');
        $mesLabel = ucfirst($mesInicio->locale('es')->translatedFormat('F Y'));

        return view('admin.pagos.index', compact(
            'pagos',
            'q',
            'resumen',
            'mes',
            'meses',
            'mesAnterior',
            'mesSiguiente',
            'mesLabel',
            'calendario',
            'dia'
        ));
    }

    public function show(int $id): View
    {
        $pago = Pago::query()
            ->with(['solicitud.plan', 'plan', 'cliente.usuario', 'planCliente.cliente.usuario'])
            ->findOrFail($id);

        if ($pago->solicitud && $pago->solicitud->pago) {
            $pasarela = strtoupper((string) ($pago->pasarela ?: 'PASARELA'));
            $this->comprobantes->asegurar($pago, $pago->solicitud, $pasarela);
            $pago->refresh();
        }

        return view('admin.pagos.show', compact('pago'));
    }

    public function update(AdminPagoUpdateRequest $request, int $id): RedirectResponse
    {
        $pago = Pago::query()->with('solicitud')->findOrFail($id);
        $data = $request->validated();

        $pago->update([
            'estado_pago' => $data['estado_pago'],
            'observacion_admin' => $data['observacion_admin'] ?? $pago->observacion_admin,
            'validado_por' => Auth::id(),
            'fecha_validacion' => now(),
        ]);

        $solicitud = $pago->solicitud;
        if ($solicitud && $solicitud->estado_solicitud === 'PENDIENTE') {
            if ($data['estado_pago'] === 'VALIDADO') {
                $this->aprobacion->aprobar($solicitud, $data['observacion_admin'] ?? null);
            } elseif ($data['estado_pago'] === 'RECHAZADO') {
                $this->aprobacion->rechazar($solicitud, $data['observacion_admin'] ?? null);
            }
        }

        return redirect()
            ->route('admin.pagos.show', $pago->id_pago)
            ->with('success', 'Estado del pago actualizado.');
    }
}
