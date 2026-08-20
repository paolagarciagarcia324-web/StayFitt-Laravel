<?php

namespace App\Http\Controllers;

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

        return view('welcome', compact('planes'));
    }
}
