<?php

namespace App\Services\Analitica;

use Illuminate\Support\Carbon;

class FiltroAnalitica
{
    public function __construct(
        public readonly Carbon $desde,
        public readonly Carbon $hasta,
        public readonly ?string $modalidad = null,
        public readonly ?string $estado = null,
        public readonly ?string $pasarela = null,
        public readonly ?int $idPlan = null,
        public readonly ?int $idUser = null,
        public readonly ?string $categoria = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $hasta = ! empty($data['fecha_fin'])
            ? Carbon::parse($data['fecha_fin'])->endOfDay()
            : Carbon::now()->endOfDay();

        $desde = ! empty($data['fecha_inicio'])
            ? Carbon::parse($data['fecha_inicio'])->startOfDay()
            : $hasta->copy()->subDays(29)->startOfDay();

        if ($desde->gt($hasta)) {
            [$desde, $hasta] = [$hasta->copy()->startOfDay(), $desde->copy()->endOfDay()];
        }

        return new self(
            desde: $desde,
            hasta: $hasta,
            modalidad: self::nullableString($data['modalidad'] ?? null),
            estado: self::nullableString($data['estado'] ?? null),
            pasarela: self::nullableString($data['pasarela'] ?? null),
            idPlan: isset($data['id_plan']) && $data['id_plan'] !== '' ? (int) $data['id_plan'] : null,
            idUser: isset($data['id_user']) && $data['id_user'] !== '' ? (int) $data['id_user'] : null,
            categoria: self::nullableString($data['categoria'] ?? null),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'fecha_inicio' => $this->desde->toDateString(),
            'fecha_fin' => $this->hasta->toDateString(),
            'modalidad' => $this->modalidad,
            'estado' => $this->estado,
            'pasarela' => $this->pasarela,
            'id_plan' => $this->idPlan,
            'id_user' => $this->idUser,
            'categoria' => $this->categoria,
        ], fn ($v) => $v !== null && $v !== '');
    }

    public function etiquetaPeriodo(): string
    {
        return $this->desde->format('d/m/Y').' – '.$this->hasta->format('d/m/Y');
    }

    private static function nullableString(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return $value === null || $value === '' ? null : (string) $value;
    }
}
