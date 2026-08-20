<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PagoController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $pagos = Pago::query()
            ->with('solicitud')
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
            ->orderByDesc('creado_en')
            ->paginate(25)
            ->withQueryString();

        return view('admin.pagos.index', compact('pagos', 'q'));
    }
}
