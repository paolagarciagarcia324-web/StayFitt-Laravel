<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use Illuminate\View\View;

class RolController extends Controller
{
    public function index(): View
    {
        $roles = Rol::query()
            ->withCount('usuarios')
            ->orderBy('id_rol')
            ->get();

        return view('admin.roles.index', compact('roles'));
    }
}
