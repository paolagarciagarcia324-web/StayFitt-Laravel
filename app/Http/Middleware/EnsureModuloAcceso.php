<?php

namespace App\Http\Middleware;

use App\Services\ModuloAccesoService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuloAcceso
{
    public function __construct(private ModuloAccesoService $modulos)
    {
    }

    /**
     * @param  string  $modulo  Código del módulo (COMUNICACION, ENTRENAMIENTO...)
     */
    public function handle(Request $request, Closure $next, string $modulo): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->tieneRol('ADMIN') || $user->tieneRol('COACH') || $user->tieneRol('ASISTENTE_TECNICO')) {
            return $next($request);
        }

        if (! $this->modulos->usuarioTieneModulo($user, $modulo)) {
            abort(403, 'Tu plan no incluye acceso a este módulo o está vencido.');
        }

        return $next($request);
    }
}
