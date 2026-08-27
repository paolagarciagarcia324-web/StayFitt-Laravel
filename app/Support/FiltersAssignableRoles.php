<?php

namespace App\Support;

use App\Models\Rol;
use Illuminate\Support\Collection;

final class FiltersAssignableRoles
{
    /** Roles que no pueden asignarse desde gestión de clientes/usuarios Admin. */
    private const BLOCKED = ['ADMIN'];

    /**
     * @param  array<int>  $roleIds
     * @return array<int>
     */
    public static function sanitize(array $roleIds): array
    {
        if ($roleIds === []) {
            return [];
        }

        $blockedIds = Rol::query()
            ->where(function ($q) {
                foreach (self::BLOCKED as $code) {
                    $q->orWhere('codigo', $code)->orWhere('codigo_rol', $code);
                }
            })
            ->pluck('id_rol')
            ->all();

        return array_values(array_diff($roleIds, $blockedIds));
    }

    /**
     * Roles visibles en formularios de asignación (sin ADMIN).
     */
    public static function forForms(): Collection
    {
        return Rol::query()
            ->orderBy('id_rol')
            ->get()
            ->filter(fn (Rol $rol) => ! in_array(strtoupper((string) ($rol->codigo ?: $rol->codigo_rol)), self::BLOCKED, true));
    }

    public static function adminRoleId(): ?int
    {
        return Rol::query()
            ->where('codigo', 'ADMIN')
            ->orWhere('codigo_rol', 'ADMIN')
            ->value('id_rol');
    }
}
