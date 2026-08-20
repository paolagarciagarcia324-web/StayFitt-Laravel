<?php

namespace App\Services;

use App\Models\TokenRecuperacion;
use App\Models\User;
use App\Notifications\RecuperarContrasenaNotification;
use Illuminate\Support\Str;

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

        TokenRecuperacion::query()->create([
            'id_user' => $user->id_user,
            'token_hash' => hash('sha256', $token),
            'expira_en' => now()->addMinutes(60),
            'ip_solicitud' => $ip,
            'creado_en' => now(),
        ]);

        $user->notify(new RecuperarContrasenaNotification($token));
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

        $user->password_hash = $password;
        $user->save();

        $registro->update(['usado_en' => now()]);

        return true;
    }

    private function buscarUsuario(string $correo): ?User
    {
        return User::query()
            ->where('correo', $correo)
            ->orWhere('email', $correo)
            ->first();
    }
}
