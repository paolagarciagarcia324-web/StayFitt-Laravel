<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * @param  string  ...$roles  Códigos de rol permitidos (ADMIN, COACH, CLIENTE...)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $allowed = collect($roles)
            ->flatMap(fn (string $rol) => preg_split('/[|,]/', $rol) ?: [])
            ->map(fn (string $rol) => strtoupper(trim($rol)))
            ->filter()
            ->unique()
            ->values();

        foreach ($allowed as $rol) {
            if ($user->tieneRol($rol)) {
                return $next($request);
            }
        }

        abort(403, 'No tienes permiso para acceder a este módulo.');
    }
}
