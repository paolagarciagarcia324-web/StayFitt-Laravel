<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class AnalisisController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.estadisticas.index');
    }
}
