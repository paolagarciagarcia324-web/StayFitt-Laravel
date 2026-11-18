<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\Auth\LoginUsuarioService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $identifier = trim($this->string('email')->toString());
        $password = $this->string('password')->toString();

        /** @var LoginUsuarioService $loginService */
        $loginService = app(LoginUsuarioService::class);

        /** @var User|null $user */
        $user = $loginService->findByLogin($identifier);

        if (! $user || ! $loginService->credentialsMatch($user, $password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Credenciales incorrectas o usuario no encontrado.',
            ]);
        }

        $estado = $user->estado ?: $user->estado_cuenta;

        if (in_array($estado, ['BLOQUEADO', 'SUSPENDIDO'], true)) {
            throw ValidationException::withMessages([
                'email' => 'Tu cuenta está bloqueada o suspendida. Contacta al administrador.',
            ]);
        }

        if ($estado === 'INACTIVO') {
            throw ValidationException::withMessages([
                'email' => 'Tu cuenta está inactiva.',
            ]);
        }

        $esPrimerAccesoConDocumento = ! $user->validatePassword($password) && $loginService->esPrimerIngreso($user);

        Auth::login($user);

        $user->forceFill(['ultimo_acceso_en' => now()])->save();

        if ($esPrimerAccesoConDocumento) {
            session()->flash('status', 'Has ingresado con tu documento por primera vez. Te recomendamos definir tu contraseña personal.');
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Demasiados intentos. Espera {$seconds} segundos.",
        ]);
    }

    public function throttleKey(): string
    {
        $identifier = Str::lower(trim($this->string('email')->toString()));

        return Str::transliterate($identifier.'|'.$this->ip());
    }
}
