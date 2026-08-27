<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminPagoUpdateRequest;
use App\Models\Pago;
use App\Services\ComprobantePasarelaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PagoController extends Controller
{
    public function __construct(private ComprobantePasarelaService $comprobantes)
    {
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $pagos = Pago::query()
            ->with(['solicitud', 'plan', 'planCliente'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('referencia', 'like', "%{$q}%")
                        ->orWhere('referencia_pago', 'like', "%{$q}%")
                        ->orWhereHas('solicitud', function ($s) use ($q) {
                            $s->where('correo', 'like', "%{$q}%")
                                ->orWhere('codigo_solicitud', 'like', "%{$q}%");
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

        return view('admin.pagos.index', compact('pagos', 'q'));
    }

    public function show(int $id): View
    {
        $pago = Pago::query()
            ->with(['solicitud.plan', 'plan', 'planCliente.cliente.usuario'])
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

        return redirect()
            ->route('admin.pagos.show', $pago->id_pago)
            ->with('success', 'Estado del pago actualizado.');
    }
}
