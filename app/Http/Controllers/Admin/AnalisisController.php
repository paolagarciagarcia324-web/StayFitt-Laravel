<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminAnaliticaFiltroRequest;
use App\Services\Analitica\FiltroAnalitica;
use App\Services\Analitica\IndicadorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnalisisController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.estadisticas.index');
    }

    public function estadisticas(AdminAnaliticaFiltroRequest $request, IndicadorService $indicadores): View
    {
        $filtro = FiltroAnalitica::fromArray($request->validated());
        $dashboard = $indicadores->dashboard($filtro);

        return view('admin.estadisticas.index', [
            'dashboard' => $dashboard,
            'filtro' => $filtro,
        ]);
    }
}
