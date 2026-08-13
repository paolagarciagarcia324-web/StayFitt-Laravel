<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use Illuminate\View\View;

class PagoController extends Controller
{
    public function index(): View
    {
        $pagos = Pago::with('solicitud')->orderByDesc('creado_en')->paginate(25);

        return view('admin.pagos.index', compact('pagos'));
    }
}
