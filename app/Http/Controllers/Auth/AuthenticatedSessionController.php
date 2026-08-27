<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        return $this->redirectAfterLogin($request, $user);
    }

    /**
     * Evita que un COACH caiga en /cliente/* (403) tras login si la URL intended era de otro rol.
     */
    protected function redirectAfterLogin(Request $request, ?\App\Models\User $user): RedirectResponse
    {
        if (! $user) {
            return redirect()->route('login');
        }

        $home = $this->homeRouteFor($user);
        $intended = $request->session()->pull('url.intended');

        if (is_string($intended) && $this->pathAllowedForUser($user, $intended)) {
            return redirect()->to($intended);
        }

        return redirect()->to($home);
    }

    protected function homeRouteFor(\App\Models\User $user): string
    {
        if ($user->tieneRol('ADMIN')) {
            return route('admin.dashboard', absolute: false);
        }

        if ($user->tieneRol('ASISTENTE_TECNICO')) {
            return route('staff.dashboard', absolute: false);
        }

        if ($user->tieneRol('COACH')) {
            return route('coach.dashboard', absolute: false);
        }

        if ($user->tieneRol('RESPONSABLE_INSTITUCIONAL')) {
            return route('institucional.dashboard', absolute: false);
        }

        if ($user->tieneRol('CLIENTE') || $user->tieneRol('CLIENTE_INSTITUCIONAL')) {
            return route('cliente.dashboard', absolute: false);
        }

        return route('dashboard', absolute: false);
    }

    protected function pathAllowedForUser(\App\Models\User $user, string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '/';
        $path = '/'.trim((string) $path, '/');
        if ($path === '/') {
            return true;
        }

        $allowedPrefixes = [];

        if ($user->tieneRol('ADMIN')) {
            $allowedPrefixes[] = '/admin';
        }

        if ($user->tieneRol('ASISTENTE_TECNICO')) {
            $allowedPrefixes[] = '/staff';
            $allowedPrefixes[] = '/coach';
        }

        if ($user->tieneRol('COACH')) {
            $allowedPrefixes[] = '/coach';
        }

        if ($user->tieneRol('RESPONSABLE_INSTITUCIONAL')) {
            $allowedPrefixes[] = '/institucional';
        }

        if ($user->tieneRol('CLIENTE') || $user->tieneRol('CLIENTE_INSTITUCIONAL')) {
            $allowedPrefixes[] = '/cliente';
        }

        foreach ($allowedPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
