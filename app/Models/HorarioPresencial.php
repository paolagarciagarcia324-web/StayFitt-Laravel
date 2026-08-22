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

    public static function codigoDesdeHora(string $horaInicio): string
    {
        try {
            $c = Carbon::parse($horaInicio);
        } catch (\Throwable) {
            return 'H-'.strtoupper(Str::random(4));
        }

        $prefijo = ((int) $c->format('H')) < 12 ? 'AM' : 'PM';

        return $prefijo.'-'.$c->format('H');
    }
}
