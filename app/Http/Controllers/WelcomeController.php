<?php

namespace App\Http\Controllers;

use App\Models\NovedadWelcome;
use App\Models\Plan;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function index(): View
    {
        $planes = Plan::activos()
            ->where('modalidad', 'VIRTUAL')
            ->with('modulos')
            ->get();

        $novedades = NovedadWelcome::query()
            ->visibles()
            ->with('plan')
            ->get();

        return view('welcome', compact('planes', 'novedades'));
    }
}
