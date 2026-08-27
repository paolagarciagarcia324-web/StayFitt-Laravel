<?php

namespace App\Services\Analitica;

use App\Models\OrdenDetalle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SerieTemporalService
{
    /**
     * @return array{labels: list<string>, values: list<float|int>}
     */
    public function serieDiaria(
        string $tabla,
        string $columnaFecha,
        FiltroAnalitica $filtro,
        string $agregado = 'COUNT(*)',
        ?callable $aplicarFiltros = null
    ): array {
        $desde = $filtro->desde->copy()->startOfDay();
        $hasta = $filtro->hasta->copy()->endOfDay();
        $dias = min(90, max(1, $desde->diffInDays($hasta) + 1));

        try {
            $query = DB::table($tabla)
                ->selectRaw("DATE({$columnaFecha}) as dia, {$agregado} as valor")
                ->whereBetween($columnaFecha, [$desde, $hasta]);

            if ($aplicarFiltros) {
                $aplicarFiltros($query);
            }

            $filas = $query->groupBy('dia')->pluck('valor', 'dia');
        } catch (\Throwable) {
            return $this->serieVacia($desde, $dias);
        }

        return $this->completar($desde, $dias, $filas);
    }

    /**
     * @return array{labels: list<string>, values: list<float|int>}
     */
    public function ingresosValidados(FiltroAnalitica $filtro): array
    {
        return $this->serieDiaria(
            'pagos',
            'creado_en',
            $filtro,
            "SUM(CASE WHEN estado_pago = 'VALIDADO' THEN monto ELSE 0 END)",
            function ($q) use ($filtro) {
                if ($filtro->pasarela) {
                    $q->where('pasarela', $filtro->pasarela);
                }
                if ($filtro->estado) {
                    $q->where('estado_pago', $filtro->estado);
                }
            }
        );
    }

    /**
     * @return array{labels: list<string>, values: list<float|int>}
     */
    public function mensajesEnviados(FiltroAnalitica $filtro): array
    {
        return $this->serieDiaria('mensajes', 'enviado_en', $filtro);
    }

    /**
     * @return array{labels: list<string>, values: list<float|int>}
     */
    public function planesVendidos(FiltroAnalitica $filtro): array
    {
        return $this->serieDiaria('planes_cliente', 'creado_en', $filtro);
    }

    /**
     * @return array{labels: list<string>, values: list<float|int>}
     */
    public function productosVendidos(FiltroAnalitica $filtro): array
    {
        $desde = $filtro->desde->copy()->startOfDay();
        $hasta = $filtro->hasta->copy()->endOfDay();
        $dias = min(90, max(1, $desde->diffInDays($hasta) + 1));

        try {
            $filas = OrdenDetalle::query()
                ->join('ordenes_tienda as o', 'o.id_orden', '=', 'orden_detalle.id_orden')
                ->selectRaw('DATE(o.creado_en) as dia, SUM(orden_detalle.cantidad) as valor')
                ->whereBetween('o.creado_en', [$desde, $hasta])
                ->groupBy('dia')
                ->pluck('valor', 'dia');
        } catch (\Throwable) {
            return $this->serieVacia($desde, $dias);
        }

        return $this->completar($desde, $dias, $filas);
    }

    /**
     * @return array{labels: list<string>, values: list<float|int>}
     */
    public function usuariosRegistrados(FiltroAnalitica $filtro): array
    {
        return $this->serieDiaria('usuarios', 'creado_en', $filtro);
    }

    /**
     * @param  \Illuminate\Support\Collection<string, mixed>  $filas
     * @return array{labels: list<string>, values: list<float|int>}
     */
    private function completar(Carbon $desde, int $dias, $filas): array
    {
        $labels = [];
        $values = [];

        for ($i = 0; $i < $dias; $i++) {
            $dia = $desde->copy()->addDays($i);
            $key = $dia->toDateString();
            $labels[] = $dia->format('d/m');
            $values[] = (float) ($filas[$key] ?? 0);
        }

        return compact('labels', 'values');
    }

    /**
     * @return array{labels: list<string>, values: list<float|int>}
     */
    private function serieVacia(Carbon $desde, int $dias): array
    {
        return $this->completar($desde, $dias, collect());
    }
}
