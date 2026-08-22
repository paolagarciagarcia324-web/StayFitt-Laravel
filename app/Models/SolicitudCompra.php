<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SolicitudCompra extends Model
{
    protected $table = 'solicitudes_compra';

    protected $primaryKey = 'id_solicitud';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'acepto_terminos' => 'boolean',
        'fecha_aceptacion_terminos' => 'datetime',
        'fecha_revision' => 'datetime',
        'revisada_en' => 'datetime',
        'datos_extra_json' => 'array',
        'precio_lista' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'reserva_cupo' => 'boolean',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    protected $fillable = [
        'codigo_solicitud',
        'id_plan',
        'id_institucion',
        'origen',
        'modalidad',
        'tipo_cliente',
        'nombres',
        'apellidos',
        'tipo_documento',
        'documento',
        'documento_identidad',
        'correo',
        'telefono',
        'ciudad',
        'precio_lista',
        'descuento',
        'total',
        'acepto_terminos',
        'fecha_aceptacion_terminos',
        'observacion_usuario',
        'datos_extra_json',
        'estado_solicitud',
        'reserva_cupo',
        'revisado_por',
        'fecha_revision',
        'observacion_admin',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan', 'id_plan');
    }

    public function pago(): HasOne
    {
        return $this->hasOne(Pago::class, 'id_solicitud', 'id_solicitud');
    }

    /** Fecha unificada de revisión (fecha_revision o revisada_en). */
    public function fechaRevision(): ?\Carbon\CarbonInterface
    {
        return $this->fecha_revision ?? $this->revisada_en;
    }

    /**
     * Serial secuencial único: Fit001, Fit002, …
     */
    public static function generarCodigo(string $modalidad = '', string $nombres = '', string $apellidos = ''): string
    {
        $max = 0;
        static::query()
            ->where('codigo_solicitud', 'like', 'Fit%')
            ->pluck('codigo_solicitud')
            ->each(function (string $codigo) use (&$max) {
                if (preg_match('/^Fit(\d+)$/i', $codigo, $m)) {
                    $max = max($max, (int) $m[1]);
                }
            });

        for ($n = $max + 1; $n < $max + 10000; $n++) {
            $codigo = 'Fit'.str_pad((string) $n, 3, '0', STR_PAD_LEFT);
            if (! static::query()->where('codigo_solicitud', $codigo)->exists()) {
                return $codigo;
            }
        }

        return 'Fit'.str_pad((string) (time() % 100000), 5, '0', STR_PAD_LEFT);
    }

    /** @return array{0: string, 1: string}|null [Fit, 001] */
    public function codigoPartes(): ?array
    {
        if (preg_match('/^(Fit)(\d+)$/i', (string) $this->codigo_solicitud, $m)) {
            return ['Fit', $m[2]];
        }

        return null;
    }
}
