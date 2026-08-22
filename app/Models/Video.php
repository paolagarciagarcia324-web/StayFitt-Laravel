<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    protected $table = 'videos';

    protected $primaryKey = 'id_video';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $casts = [
        'requiere_ia' => 'boolean',
    ];

    protected $fillable = [
        'id_categoria_video',
        'dia_orden',
        'titulo',
        'descripcion',
        'url_video',
        'duracion_segundos',
        'requiere_ia',
        'estado'
    ];
    public function categoriaVideo(): BelongsTo
    {
        return $this->belongsTo(CategoriaVideo::class, 'id_categoria_video', 'id_categoria_video');
    }
}
