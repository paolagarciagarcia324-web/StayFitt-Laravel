<?php

namespace App\Services;

use App\Models\AccesoClienteModulo;
use App\Models\Cliente;
use App\Models\ModuloServicio;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ModuloAccesoService
{
    public function clienteEstaOperativo(Cliente $cliente): bool
    {
        return in_array(strtoupper((string) $cliente->estado_cliente), ['ACTIVA', 'ACTIVO'], true);
    }

    public function clienteTieneModulo(Cliente $cliente, string $codigo): bool
    {
        if (! $this->clienteEstaOperativo($cliente)) {
            return false;
        }

        $codigo = strtoupper(trim($codigo));
        $hoy = Carbon::now()->toDateString();

        return AccesoClienteModulo::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('estado_acceso', 'ACTIVO')
            ->where('fecha_fin', '>=', $hoy)
            ->whereHas('modulo', function ($query) use ($codigo) {
                $query->where('codigo', $codigo)
                    ->orWhere('codigo_modulo', $codigo);
            })
            ->exists();
    }

    public function usuarioTieneModulo(User $user, string $codigo): bool
    {
        $cliente = $user->cliente;

        if (! $cliente) {
            return false;
        }

        return $this->clienteTieneModulo($cliente, $codigo);
    }

    /**
     * @return Collection<int, ModuloServicio>
     */
    public function modulosActivosCliente(Cliente $cliente): Collection
    {
        if (! $this->clienteEstaOperativo($cliente)) {
            return collect();
        }

        $hoy = Carbon::now()->toDateString();

        $ids = AccesoClienteModulo::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('estado_acceso', 'ACTIVO')
            ->where('fecha_fin', '>=', $hoy)
            ->pluck('id_modulo')
            ->unique()
            ->filter();

        if ($ids->isEmpty()) {
            return collect();
        }

        return ModuloServicio::query()
            ->whereIn('id_modulo', $ids)
            ->where(function ($q) {
                $q->where('estado', 'ACTIVO')->orWhereNull('estado');
            })
            ->orderBy('orden')
            ->get();
    }
}
