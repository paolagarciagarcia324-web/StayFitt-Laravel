<?php

namespace App\Services\Analitica;

use App\Models\Cliente;
use App\Models\InvitacionWhatsapp;
use App\Models\Mensaje;
use App\Models\OrdenTienda;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\ReporteGenerado;
use App\Models\SolicitudCompra;
use App\Models\UsoCupon;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteExportService
{
    public const TIPOS = [
        'pagos' => 'Pagos y pasarela',
        'clientes' => 'Clientes y planes',
        'cupos' => 'Cupos por plan',
        'solicitudes' => 'Solicitudes comerciales',
        'cupones' => 'Uso de cupones',
        'tienda' => 'Pedidos tienda',
        'comunicacion' => 'Comunicación (mensajes / WA)',
    ];

    public const FORMATOS = ['csv', 'excel', 'pdf', 'web'];

    /**
     * @return array{titulo: string, headers: list<string>, rows: Collection<int, list<mixed>>, count: int}
     */
    public function construir(string $tipo, FiltroAnalitica $filtro): array
    {
        return match ($tipo) {
            'pagos' => $this->reportePagos($filtro),
            'clientes' => $this->reporteClientes($filtro),
            'cupos' => $this->reporteCupos($filtro),
            'solicitudes' => $this->reporteSolicitudes($filtro),
            'cupones' => $this->reporteCupones($filtro),
            'tienda' => $this->reporteTienda($filtro),
            'comunicacion' => $this->reporteComunicacion($filtro),
            default => throw new \InvalidArgumentException('Tipo de reporte no válido.'),
        };
    }

    public function preview(string $tipo, FiltroAnalitica $filtro, int $limit = 50): array
    {
        $data = $this->construir($tipo, $filtro);

        return [
            'titulo' => $data['titulo'],
            'headers' => $data['headers'],
            'rows' => $data['rows']->take($limit)->values(),
            'count' => $data['count'],
            'periodo' => $filtro->etiquetaPeriodo(),
            'generado_en' => now()->format('d/m/Y H:i'),
        ];
    }

    public function exportar(string $tipo, string $formato, FiltroAnalitica $filtro): StreamedResponse|Response
    {
        $data = $this->construir($tipo, $filtro);
        $stamp = now()->format('Ymd_His');
        $base = "stayfit_{$tipo}_{$stamp}";

        $reporte = ReporteGenerado::create([
            'id_user' => Auth::id(),
            'tipo' => strtoupper($tipo),
            'titulo' => $data['titulo'],
            'parametros_json' => array_merge($filtro->toArray(), ['formato' => $formato]),
            'formato' => strtoupper($formato === 'excel' ? 'XLS' : $formato),
            'estado' => 'GENERADO',
            'registros_count' => $data['count'],
            'url_archivo' => null,
            'generado_en' => now(),
        ]);

        return match ($formato) {
            'csv' => $this->streamCsv($data, $base.'.csv', $reporte),
            'excel' => $this->streamExcel($data, $base.'.xls', $reporte, $filtro),
            'pdf' => $this->streamPdfView($data, $reporte, $filtro),
            default => throw new \InvalidArgumentException('Formato no válido.'),
        };
    }

    private function reportePagos(FiltroAnalitica $filtro): array
    {
        $q = Pago::query()
            ->with(['cliente.usuario', 'plan'])
            ->whereBetween('creado_en', [$filtro->desde, $filtro->hasta])
            ->when($filtro->estado, fn ($q) => $q->where('estado_pago', $filtro->estado))
            ->when($filtro->pasarela, fn ($q) => $q->where('pasarela', $filtro->pasarela))
            ->when($filtro->idPlan, fn ($q) => $q->where('id_plan', $filtro->idPlan))
            ->orderByDesc('id_pago');

        $rows = $q->limit(5000)->get()->map(fn (Pago $p) => [
            $p->id_pago,
            $p->referencia ?: $p->referencia_pago,
            $p->monto,
            $p->moneda,
            $p->estado_pago,
            $p->metodo_pago ?: $p->metodo,
            $p->pasarela,
            $p->cliente?->usuario?->correo,
            optional($p->creado_en)->format('Y-m-d H:i'),
        ]);

        return [
            'titulo' => 'Reporte de pagos',
            'headers' => ['ID', 'Referencia', 'Monto', 'Moneda', 'Estado', 'Método', 'Pasarela', 'Correo', 'Fecha'],
            'rows' => $rows,
            'count' => $rows->count(),
        ];
    }

    private function reporteClientes(FiltroAnalitica $filtro): array
    {
        $q = Cliente::query()
            ->with(['usuario', 'planesCliente.plan'])
            ->when($filtro->modalidad, fn ($q) => $q->where('modalidad', $filtro->modalidad))
            ->when($filtro->estado, fn ($q) => $q->where('estado_cliente', $filtro->estado))
            ->when($filtro->idUser, fn ($q) => $q->where('id_user', $filtro->idUser))
            ->orderByDesc('id_cliente');

        $rows = $q->limit(5000)->get()->map(function (Cliente $c) {
            $plan = $c->planesCliente->firstWhere('estado_plan_cliente', 'ACTIVO')
                ?? $c->planesCliente->sortByDesc('fecha_fin')->first();

            return [
                $c->id_cliente,
                $c->usuario?->nombres,
                $c->usuario?->apellidos,
                $c->usuario?->correo,
                $c->modalidad,
                $c->estado_cliente,
                $plan?->plan?->nombre,
                $plan?->fecha_fin,
            ];
        });

        return [
            'titulo' => 'Reporte de clientes',
            'headers' => ['ID', 'Nombres', 'Apellidos', 'Correo', 'Modalidad', 'Estado', 'Plan', 'Vence'],
            'rows' => $rows,
            'count' => $rows->count(),
        ];
    }

    private function reporteCupos(FiltroAnalitica $filtro): array
    {
        $q = Plan::query()
            ->when($filtro->modalidad, fn ($q) => $q->where('modalidad', $filtro->modalidad))
            ->when($filtro->estado, fn ($q) => $q->where('estado_plan', $filtro->estado))
            ->when($filtro->idPlan, fn ($q) => $q->where('id_plan', $filtro->idPlan))
            ->orderBy('orden');

        $rows = $q->get()->map(fn (Plan $p) => [
            $p->nombre,
            $p->modalidad,
            $p->cupos_totales,
            $p->cupos_ocupados,
            $p->cupos_reservados,
            $p->estado_plan,
        ]);

        return [
            'titulo' => 'Reporte de cupos',
            'headers' => ['Plan', 'Modalidad', 'Totales', 'Ocupados', 'Reservados', 'Estado'],
            'rows' => $rows,
            'count' => $rows->count(),
        ];
    }

    private function reporteSolicitudes(FiltroAnalitica $filtro): array
    {
        $q = SolicitudCompra::query()
            ->with('plan')
            ->whereBetween('creado_en', [$filtro->desde, $filtro->hasta])
            ->when($filtro->estado, fn ($q) => $q->where('estado_solicitud', $filtro->estado))
            ->when($filtro->modalidad, fn ($q) => $q->where('modalidad', $filtro->modalidad))
            ->orderByDesc('id_solicitud');

        $rows = $q->limit(5000)->get()->map(fn (SolicitudCompra $s) => [
            $s->codigo_solicitud,
            $s->nombres,
            $s->apellidos,
            $s->correo,
            $s->modalidad,
            $s->estado_solicitud,
            $s->plan?->nombre,
            optional($s->creado_en)->format('Y-m-d H:i'),
        ]);

        return [
            'titulo' => 'Reporte de solicitudes',
            'headers' => ['Código', 'Nombres', 'Apellidos', 'Correo', 'Modalidad', 'Estado', 'Plan', 'Fecha'],
            'rows' => $rows,
            'count' => $rows->count(),
        ];
    }

    private function reporteCupones(FiltroAnalitica $filtro): array
    {
        $q = UsoCupon::query()
            ->with('descuento')
            ->whereBetween('usado_en', [$filtro->desde, $filtro->hasta])
            ->orderByDesc('id_uso_cupon');

        $rows = $q->limit(5000)->get()->map(fn (UsoCupon $u) => [
            $u->descuento?->codigo,
            $u->correo,
            $u->id_solicitud,
            optional($u->usado_en)->format('Y-m-d H:i'),
        ]);

        return [
            'titulo' => 'Reporte de cupones',
            'headers' => ['Cupón', 'Correo', 'Solicitud', 'Usado en'],
            'rows' => $rows,
            'count' => $rows->count(),
        ];
    }

    private function reporteTienda(FiltroAnalitica $filtro): array
    {
        $q = OrdenTienda::query()
            ->with('cliente.usuario')
            ->whereBetween('creado_en', [$filtro->desde, $filtro->hasta])
            ->when($filtro->estado, fn ($q) => $q->where('estado_orden', $filtro->estado))
            ->orderByDesc('id_orden');

        $rows = $q->limit(5000)->get()->map(fn (OrdenTienda $o) => [
            $o->numero_orden,
            $o->cliente?->usuario?->nombres.' '.$o->cliente?->usuario?->apellidos,
            $o->total,
            $o->estado_orden,
            optional($o->creado_en)->format('Y-m-d H:i'),
        ]);

        return [
            'titulo' => 'Reporte de tienda',
            'headers' => ['Orden', 'Cliente', 'Total', 'Estado', 'Fecha'],
            'rows' => $rows,
            'count' => $rows->count(),
        ];
    }

    private function reporteComunicacion(FiltroAnalitica $filtro): array
    {
        $mensajes = Mensaje::query()
            ->with(['emisor', 'chat'])
            ->whereBetween('enviado_en', [$filtro->desde, $filtro->hasta])
            ->orderByDesc('id_mensaje')
            ->limit(3000)
            ->get()
            ->map(fn (Mensaje $m) => [
                'MENSAJE',
                $m->chat?->nombre ?: ('Chat #'.$m->id_chat),
                $m->emisor?->nombres.' '.$m->emisor?->apellidos,
                mb_substr((string) $m->contenido, 0, 120),
                optional($m->enviado_en)->format('Y-m-d H:i'),
            ]);

        $inv = InvitacionWhatsapp::query()
            ->with(['cliente.usuario', 'grupoWa'])
            ->whereBetween('mostrada_en', [$filtro->desde, $filtro->hasta])
            ->orderByDesc('id_invitacion_wa')
            ->limit(2000)
            ->get()
            ->map(fn (InvitacionWhatsapp $i) => [
                'WHATSAPP',
                $i->grupoWa?->nombre ?: 'Link directo',
                $i->cliente?->usuario?->nombres.' '.$i->cliente?->usuario?->apellidos,
                $i->aceptada_en ? 'ACEPTADA' : ($i->cerrada_sin_aceptar ? 'CERRADA' : 'PENDIENTE'),
                optional($i->mostrada_en)->format('Y-m-d H:i'),
            ]);

        $rows = $mensajes->concat($inv)->values();

        return [
            'titulo' => 'Reporte de comunicación',
            'headers' => ['Tipo', 'Contexto', 'Usuario', 'Detalle', 'Fecha'],
            'rows' => $rows,
            'count' => $rows->count(),
        ];
    }

    private function streamCsv(array $data, string $filename, ReporteGenerado $reporte): StreamedResponse
    {
        $reporte->update(['url_archivo' => 'reportes/'.$filename]);

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, $data['headers']);
            foreach ($data['rows'] as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function streamExcel(array $data, string $filename, ReporteGenerado $reporte, FiltroAnalitica $filtro): StreamedResponse
    {
        $reporte->update(['url_archivo' => 'reportes/'.$filename]);

        return response()->streamDownload(function () use ($data, $filtro) {
            echo '<html><head><meta charset="UTF-8"></head><body>';
            echo '<h2>'.e($data['titulo']).'</h2>';
            echo '<p>Periodo: '.e($filtro->etiquetaPeriodo()).' · Generado: '.e(now()->format('d/m/Y H:i')).'</p>';
            echo '<table border="1" cellpadding="4" cellspacing="0"><thead><tr>';
            foreach ($data['headers'] as $h) {
                echo '<th>'.e((string) $h).'</th>';
            }
            echo '</tr></thead><tbody>';
            foreach ($data['rows'] as $row) {
                echo '<tr>';
                foreach ($row as $cell) {
                    echo '<td>'.e((string) $cell).'</td>';
                }
                echo '</tr>';
            }
            echo '</tbody></table></body></html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    private function streamPdfView(array $data, ReporteGenerado $reporte, FiltroAnalitica $filtro): Response
    {
        $html = view('admin.reportes.pdf', [
            'titulo' => $data['titulo'],
            'headers' => $data['headers'],
            'rows' => $data['rows'],
            'count' => $data['count'],
            'periodo' => $filtro->etiquetaPeriodo(),
            'generado_en' => now()->format('d/m/Y H:i'),
        ])->render();

        $filename = 'stayfit_reporte_'.now()->format('Ymd_His').'.html';
        Storage::disk('local')->put('reportes/'.$filename, $html);
        $reporte->update(['url_archivo' => 'reportes/'.$filename, 'formato' => 'PDF']);

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
