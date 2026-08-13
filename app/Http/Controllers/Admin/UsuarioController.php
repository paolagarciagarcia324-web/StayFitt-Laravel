<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(): View
    {
        $usuarios = User::with('roles')->orderByDesc('creado_en')->paginate(20);

        return view('admin.usuarios.index', compact('usuarios'));
    }
}
