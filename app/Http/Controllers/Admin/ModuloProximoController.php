<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ModuloProximoController extends Controller
{
    public function eventos(): View
    {
        return $this->pantalla('Eventos', 'Actividades, clases especiales y encuentros FigueFit');
    }

    public function calendario(): View
    {
        return $this->pantalla('Calendario', 'Agenda global de horarios, sesiones y eventos');
    }

    public function comunidad(): View
    {
        return $this->pantalla('Comunidad', 'Chats de coaches, clientes y el resto de la plataforma');
    }

    public function estadisticas(): View
    {
        return $this->pantalla('Estadísticas', 'Indicadores de operación, retención y ocupación');
    }

    public function reportes(): View
    {
        return $this->pantalla('Reportes', 'Informes exportables de clientes, pagos y tienda');
    }

    private function pantalla(string $title, string $subtitle): View
    {
        return view('admin.proximo', compact('title', 'subtitle'));
    }
}
