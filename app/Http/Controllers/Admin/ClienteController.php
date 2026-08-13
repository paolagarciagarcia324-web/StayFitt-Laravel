<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(): View
    {
        $clientes = Cliente::with('usuario')->orderByDesc('creado_en')->paginate(20);

        return view('admin.clientes.index', compact('clientes'));
    }
}
