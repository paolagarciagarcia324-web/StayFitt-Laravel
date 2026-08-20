<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function etiqueta(): string
    {
        $inicio = substr((string) $this->hora_inicio, 0, 5);
        $fin = substr((string) $this->hora_fin, 0, 5);

        return "{$this->nombre} · {$this->dias} · {$inicio} – {$fin}";
    }

    public function tieneCupo(): bool
    {
        return (int) $this->cupo_ocupado < (int) $this->cupo_maximo;
    }
}
