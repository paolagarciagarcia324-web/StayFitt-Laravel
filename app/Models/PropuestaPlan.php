<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropuestaPlan extends Model
{
    protected $table = 'propuestas_plan';

    protected $primaryKey = 'id_propuesta';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    public const ESTADO_BORRADOR_ENTRENAMIENTO = 'BORRADOR_ENTRENAMIENTO';

    public const ESTADO_BORRADOR_NUTRICION = 'BORRADOR_NUTRICION';

    public const ESTADO_PENDIENTE_REVISION = 'PENDIENTE_REVISION';

    public const ESTADO_APROBADO = 'APROBADO';

    public const ESTADO_RECHAZADO = 'RECHAZADO';

    public const ESTADO_PUBLICADO = 'PUBLICADO';

    protected $casts = [
        'enviado_en' => 'datetime',
        'revisado_en' => 'datetime',
        'publicado_en' => 'datetime',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    protected $fillable = [
        'id_coach',
        'creado_por',
        'id_programa',
        'id_plan_nutricional',
        'id_plan_publicado',
        'titulo_interno',
        'descripcion_interna',
        'estado',
        'notas_coach',
        'notas_admin',
        'motivo_rechazo',
        'enviado_en',
        'revisado_en',
        'publicado_en',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_propuesta';
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'id_coach', 'id_coach');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por', 'id_user');
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(ProgramaVirtual::class, 'id_programa', 'id_programa');
    }

    public function planNutricional(): BelongsTo
    {
        return $this->belongsTo(PlanNutricional::class, 'id_plan_nutricional', 'id_plan_nutricional');
    }

    public function planPublicado(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan_publicado', 'id_plan');
    }

    public function esEditable(): bool
    {
        return in_array($this->estado, [
            self::ESTADO_BORRADOR_ENTRENAMIENTO,
            self::ESTADO_BORRADOR_NUTRICION,
            self::ESTADO_RECHAZADO,
        ], true);
    }

    public function puedeAvanzarANutricion(): bool
    {
        if (! $this->programa) {
            return false;
        }

        return $this->conteoVideos() >= 1;
    }

    public function puedeEnviarARevision(): bool
    {
        if (! $this->puedeAvanzarANutricion()) {
            return false;
        }

        $nutricion = $this->planNutricional;
        if (! $nutricion) {
            return false;
        }

        if (trim((string) $nutricion->nombre) === '' || trim((string) $nutricion->objetivo) === '') {
            return false;
        }

        return $nutricion->comidas()->exists();
    }

    public function conteoVideos(): int
    {
        $programa = $this->programa;
        if (! $programa) {
            return 0;
        }

        return Video::query()
            ->whereHas('categoriaVideo', fn ($q) => $q->where('id_programa', $programa->id_programa))
            ->where('estado', 'ACTIVO')
            ->count();
    }

    public function etiquetaEstado(): string
    {
        return match ($this->estado) {
            self::ESTADO_BORRADOR_ENTRENAMIENTO => 'Borrador · Entrenamiento',
            self::ESTADO_BORRADOR_NUTRICION => 'Borrador · Nutrición',
            self::ESTADO_PENDIENTE_REVISION => 'Pendiente de revisión',
            self::ESTADO_APROBADO => 'Aprobado',
            self::ESTADO_RECHAZADO => 'Rechazado',
            self::ESTADO_PUBLICADO => 'Publicado',
            default => $this->estado,
        };
    }
}
