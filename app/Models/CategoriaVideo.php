<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoriaVideo extends Model
{
    protected $table = 'categorias_video';

    protected $primaryKey = 'id_categoria_video';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_programa',
        'nombre',
        'orden'
    ];
    public function programa(): BelongsTo
    {
        return $this->belongsTo(ProgramaVirtual::class, 'id_programa', 'id_programa');
    }
}
