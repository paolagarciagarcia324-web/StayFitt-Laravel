<?php

namespace App\Services;

use App\Models\TokenRecuperacion;
use App\Models\User;
use App\Notifications\RecuperarContrasenaNotification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RecuperacionContrasenaService
{
    public function solicitar(string $correo, ?string $ip = null): void
    {
        $user = $this->buscarUsuario($correo);

        if (! $user) {
            return;
        }

        TokenRecuperacion::query()
            ->where('id_user', $user->id_user)
            ->whereNull('usado_en')
            ->delete();

        $token = Str::random(64);

        $registro = TokenRecuperacion::query()->create([
            'id_user' => $user->id_user,
            'token_hash' => hash('sha256', $token),
            'expira_en' => now()->addMinutes(60),
            'ip_solicitud' => $ip,
            'creado_en' => now(),
        ]);

        try {
            $user->notify(new RecuperarContrasenaNotification($token));
        } catch (\Throwable $e) {
            report($e);
            $registro->delete();

            throw ValidationException::withMessages([
                'email' => 'No pudimos enviar el correo de recuperación. Inténtalo de nuevo en unos minutos.',
            ]);
        }
    }

    public function restablecer(string $token, string $correo, string $password): bool
    {
        $user = $this->buscarUsuario($correo);

        if (! $user) {
            return false;
        }

        $registro = TokenRecuperacion::query()
            ->where('id_user', $user->id_user)
            ->whereNull('usado_en')
            ->where('token_hash', hash('sha256', $token))
            ->where('expira_en', '>', now())
            ->first();

        if (! $registro) {
            return false;
        }

        $user->assignPassword($password);
        $user->save();

        $registro->update(['usado_en' => now()]);

        TokenRecuperacion::query()
            ->where('id_user', $user->id_user)
            ->whereNull('usado_en')
            ->delete();

        return true;
    }

    private function buscarUsuario(string $correo): ?User
    {
        $correo = trim(Str::lower($correo));
        if ($correo === '') {
            return null;
        }

        return User::query()
            ->where(function ($query) use ($correo) {
                $query->whereRaw('LOWER(correo) = ?', [$correo])
                    ->orWhereRaw('LOWER(email) = ?', [$correo]);
            })
            ->first();
    }
}
