<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class HorarioPresencial extends Model
{
    protected $table = 'horarios_presenciales';

    protected $primaryKey = 'id_horario';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'activo' => 'boolean',
    ];

    protected $fillable = [
        'codigo',
        'nombre',
        'dias',
        'hora_inicio',
        'hora_fin',
        'cupo_maximo',
        'cupo_ocupado',
        'link_whatsapp',
        'activo',
        'orden',
    ];

    public function scopeActivos($query)
    {
        return $query->where('activo', true)->orderBy('orden');
    }

    public static function formatoHora12(?string $hora): string
    {
        $hora = trim((string) $hora);
        if ($hora === '') {
            return '—';
        }

        try {
            return Carbon::parse($hora)->format('g:i A');
        } catch (\Throwable) {
            $slice = substr($hora, 0, 5);

            return $slice !== '' ? $slice : '—';
        }
    }

    public function rangoHora12(): string
    {
        return self::formatoHora12((string) $this->hora_inicio).' – '.self::formatoHora12((string) $this->hora_fin);
    }

    public function nombreGrupoWhatsapp(): string
    {
        $guardado = trim((string) $this->link_whatsapp);
        if ($guardado !== '' && ! preg_match('#^https?://#i', $guardado)) {
            return $guardado;
        }

        return 'FigueFit CLUB '.self::formatoHora12((string) $this->hora_inicio);
    }

    /** Enlace de invitación al grupo (URL real o demo por código). */
    public function urlGrupoWhatsapp(): string
    {
        $guardado = trim((string) $this->link_whatsapp);
        if (preg_match('#^https?://#i', $guardado)) {
            return $guardado;
        }

        $slug = Str::lower(Str::slug((string) ($this->codigo ?: 'grupo'), ''));

        return 'https://chat.whatsapp.com/figuefit-'.($slug !== '' ? $slug : 'club');
    }

    public function etiqueta(): string
    {
        return "{$this->nombre} · {$this->dias} · {$this->rangoHora12()}";
    }

    public function tieneCupo(): bool
    {
        return (int) $this->cupo_ocupado < (int) $this->cupo_maximo;
    }

    public static function sincronizarCupo(?int $idHorario): void
    {
        if (! $idHorario) {
            return;
        }

        $horario = static::query()->find($idHorario);
        if (! $horario) {
            return;
        }

        $ocupado = Cliente::query()
            ->where('id_horario', $idHorario)
            ->whereIn('estado_cliente', ['ACTIVO', 'ACTIVA'])
            ->count();

        $horario->update(['cupo_ocupado' => $ocupado]);
    }

    public static function codigoDesdeHora(string $horaInicio): string
    {
        try {
            $c = Carbon::parse($horaInicio);
        } catch (\Throwable) {
            return 'H-'.strtoupper(Str::random(4));
        }

        $prefijo = ((int) $c->format('H')) < 12 ? 'AM' : 'PM';

        return $prefijo.'-'.$c->format('Hi');
    }

    /** Franjas oficiales Lunes–Viernes de la sala. */
    public static function franjasSala(): array
    {
        $dias = 'Lun · Mar · Mié · Jue · Vie';

        return [
            ['codigo' => 'AM-0530', 'nombre' => 'Mañana temprano', 'dias' => $dias, 'hora_inicio' => '05:30:00', 'hora_fin' => '06:30:00', 'cupo_maximo' => 18, 'orden' => 1],
            ['codigo' => 'AM-0730', 'nombre' => 'Mañana mid', 'dias' => $dias, 'hora_inicio' => '07:30:00', 'hora_fin' => '08:30:00', 'cupo_maximo' => 18, 'orden' => 2],
            ['codigo' => 'AM-0830', 'nombre' => 'Mañana funcional', 'dias' => $dias, 'hora_inicio' => '08:30:00', 'hora_fin' => '09:30:00', 'cupo_maximo' => 18, 'orden' => 3],
            ['codigo' => 'PM-1700', 'nombre' => 'Tarde fuerza', 'dias' => $dias, 'hora_inicio' => '17:00:00', 'hora_fin' => '18:00:00', 'cupo_maximo' => 20, 'orden' => 4],
            ['codigo' => 'PM-1800', 'nombre' => 'Tarde community', 'dias' => $dias, 'hora_inicio' => '18:00:00', 'hora_fin' => '19:00:00', 'cupo_maximo' => 20, 'orden' => 5],
            ['codigo' => 'PM-2000', 'nombre' => 'Noche club', 'dias' => $dias, 'hora_inicio' => '20:00:00', 'hora_fin' => '21:00:00', 'cupo_maximo' => 22, 'orden' => 6],
        ];
    }

    /** Upsert de franjas L–V y desactiva las que no estén en la lista. */
    public static function asegurarFranjasSala(): void
    {
        $codigos = [];
        foreach (self::franjasSala() as $franja) {
            $codigos[] = $franja['codigo'];
            $inicio = Carbon::parse($franja['hora_inicio']);
            $grupo = 'FigueFit CLUB '.self::formatoHora12($franja['hora_inicio']);

            $existente = static::query()->where('codigo', $franja['codigo'])->first();
            $payload = [
                'nombre' => $franja['nombre'],
                'dias' => $franja['dias'],
                'hora_inicio' => $franja['hora_inicio'],
                'hora_fin' => $franja['hora_fin'],
                'cupo_maximo' => $franja['cupo_maximo'],
                'link_whatsapp' => $grupo,
                'activo' => true,
                'orden' => $franja['orden'],
            ];

            if ($existente) {
                $existente->update($payload);
            } else {
                static::query()->create($payload + [
                    'codigo' => $franja['codigo'],
                    'cupo_ocupado' => 0,
                ]);
            }
        }

        static::query()
            ->whereNotIn('codigo', $codigos)
            ->update(['activo' => false]);
    }
}
