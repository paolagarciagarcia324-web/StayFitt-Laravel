<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use Illuminate\View\View;

class CoachController extends Controller
{
    public function index(): View
    {
        $coaches = Coach::with('usuario')->orderByDesc('creado_en')->paginate(20);

        return view('admin.coaches.index', compact('coaches'));
    }
}
