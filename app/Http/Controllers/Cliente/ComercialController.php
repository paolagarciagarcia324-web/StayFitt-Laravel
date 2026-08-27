<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\DescuentoCupon;
use App\Models\Plan;
use App\Models\SolicitudCompra;
use App\Support\ResolvesCliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComercialController extends Controller
{
    use ResolvesCliente;

    public function planes(): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $planes = Plan::query()
            ->where('estado_plan', 'ACTIVO')
            ->orderBy('precio')
            ->get();

        return view('cliente.comercial.planes', compact('cliente', 'planes'));
    }

    public function planShow(int $id): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $plan = Plan::query()->findOrFail($id);

        return view('cliente.comercial.plan-show', compact('cliente', 'plan'));
    }

    public function solicitudes(): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $user = $cliente->usuario;
        $correo = $user?->correo ?: $user?->email;

        $solicitudes = SolicitudCompra::query()
            ->with('plan')
            ->where(function ($q) use ($correo, $user, $cliente) {
                $hasFilter = false;
                if ($correo) {
                    $q->where('correo', $correo);
                    $hasFilter = true;
                }
                $doc = $cliente->documento ?: ($user?->documento_identidad ?? null);
                if ($doc) {
                    if ($hasFilter) {
                        $q->orWhere('documento', $doc)->orWhere('documento_identidad', $doc);
                    } else {
                        $q->where(function ($inner) use ($doc) {
                            $inner->where('documento', $doc)->orWhere('documento_identidad', $doc);
                        });
                        $hasFilter = true;
                    }
                }
                if (! $hasFilter) {
                    $q->whereRaw('1 = 0');
                }
            })
            ->orderByDesc('creado_en')
            ->paginate(12);

        return view('cliente.comercial.solicitudes', compact('cliente', 'solicitudes'));
    }

    public function cupones(): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $hoy = now();

        $cupones = DescuentoCupon::query()
            ->with('plan')
            ->where(function ($q) {
                $q->where('estado_cupon', 'ACTIVO')->orWhere('estado', 'ACTIVO');
            })
            ->where(function ($q) use ($hoy) {
                $q->whereNull('fecha_inicio')->orWhere('fecha_inicio', '<=', $hoy);
            })
            ->where(function ($q) use ($hoy) {
                $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $hoy);
            })
            ->orderByDesc('id_descuento')
            ->limit(30)
            ->get();

        return view('cliente.comercial.cupones', compact('cliente', 'cupones'));
    }

    public function validarCupon(Request $request): RedirectResponse
    {
        $this->resolveClienteOrAbort();
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:40'],
        ]);

        $codigo = strtoupper(trim($data['codigo']));
        $cupon = DescuentoCupon::query()
            ->whereRaw('UPPER(codigo) = ?', [$codigo])
            ->first();

        if (! $cupon) {
            return back()->withErrors(['codigo' => 'Cupón no encontrado.'])->withInput();
        }

        $activo = in_array(strtoupper((string) ($cupon->estado_cupon ?: $cupon->estado)), ['ACTIVO'], true);
        if (! $activo) {
            return back()->withErrors(['codigo' => 'Este cupón no está activo.'])->withInput();
        }

        if ($cupon->fecha_fin && $cupon->fecha_fin->isPast()) {
            return back()->withErrors(['codigo' => 'Este cupón ya venció.'])->withInput();
        }

        $max = (int) ($cupon->usos_maximos ?: $cupon->uso_maximo ?: 0);
        $usos = (int) ($cupon->usos_actuales ?? 0);
        if ($max > 0 && $usos >= $max) {
            return back()->withErrors(['codigo' => 'Este cupón alcanzó el máximo de usos.'])->withInput();
        }

        $tipo = $cupon->tipo_descuento ?: $cupon->tipo;
        $valor = (float) $cupon->valor;
        $desc = strtoupper((string) $tipo) === 'PORCENTAJE'
            ? number_format($valor, 0).'% de descuento'
            : '$'.number_format($valor, 0, ',', '.').' de descuento';

        return back()->with('success', "Cupón «{$cupon->codigo}» válido: {$desc}. Úsalo al renovar o comprar un plan.");
    }
}
