<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Autenticación contra tabla `usuarios` (correo, email o documento).
 */
class LoginUsuarioService
{
    public function findByLogin(string $identifier): ?User
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        $lower = Str::lower($identifier);

        return User::query()
            ->where(function ($query) use ($identifier, $lower) {
                $query->whereRaw('LOWER(correo) = ?', [$lower])
                    ->orWhereRaw('LOWER(email) = ?', [$lower])
                    ->orWhere('documento_identidad', $identifier);
            })
            ->first();
    }

    public function credentialsMatch(User $user, string $password): bool
    {
        if ($password === '') {
            return false;
        }

        if ($user->validatePassword($password)) {
            return true;
        }

        $documento = trim((string) ($user->documento_identidad ?? ''));
        if ($documento !== '' && hash_equals($documento, $password)) {
            return true;
        }

        $user->loadMissing('cliente');
        $docCliente = trim((string) ($user->cliente?->documento ?? ''));

        return $docCliente !== '' && hash_equals($docCliente, $password);
    }
}
