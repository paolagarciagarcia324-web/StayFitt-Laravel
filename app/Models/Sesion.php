<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sesion extends Model
{
    protected $table = 'sesiones';

    protected $primaryKey = 'id_sesion';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'fecha' => 'date',
    ];

    protected $fillable = [
        'id_coach',
        'tipo',
        'titulo',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'modalidad',
        'cupo_maximo',
        'cupo_reservado',
        'estado',
    ];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'id_coach', 'id_coach');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'id_sesion', 'id_sesion');
    }

    public function participantes(): HasMany
    {
        return $this->hasMany(SesionParticipante::class, 'id_sesion', 'id_sesion');
    }

    public static function formatoHora12(?string $hora): string
    {
        return HorarioPresencial::formatoHora12($hora);
    }

    public function rangoHora12(): string
    {
        return self::formatoHora12((string) $this->hora_inicio).' – '.self::formatoHora12((string) $this->hora_fin);
    }

    public function horaInicioCorta(): string
    {
        return self::formatoHora12((string) $this->hora_inicio);
    }
}
