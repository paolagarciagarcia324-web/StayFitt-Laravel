<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NovedadWelcome extends Model
{
    protected $table = 'novedades_welcome';

    protected $primaryKey = 'id_novedad';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'activo' => 'boolean',
        'inicia_en' => 'datetime',
        'termina_en' => 'datetime',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    protected $fillable = [
        'titulo',
        'subtitulo',
        'descripcion',
        'imagen_url',
        'tipo',
        'boton_texto',
        'url_destino',
        'id_plan',
        'activo',
        'orden',
        'inicia_en',
        'termina_en',
        'creado_por',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_novedad';
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan', 'id_plan');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por', 'id_user');
    }

    public function scopeVisibles(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('activo', true)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('inicia_en')->orWhere('inicia_en', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('termina_en')->orWhere('termina_en', '>=', $now);
            })
            ->orderBy('orden')
            ->orderByDesc('id_novedad');
    }

    public function imagenSrc(): string
    {
        $path = trim((string) $this->imagen_url);
        if ($path === '') {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return asset('storage/'.$path);
    }

    public function destinoUrl(): string
    {
        $destino = trim((string) $this->url_destino);
        if ($destino !== '') {
            if (str_starts_with($destino, '#')) {
                return url('/').$destino;
            }
            if (str_starts_with($destino, '/')) {
                return url($destino);
            }

            return $destino;
        }

        return url('/').'#planes';
    }

    public function etiquetaTipo(): string
    {
        return match ($this->tipo) {
            'NUEVO_PLAN' => 'Nuevo plan',
            'DESCUENTO' => 'Descuento',
            'ACTUALIZACION' => 'Actualización',
            default => 'Novedad',
        };
    }
}
