<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramaVirtual extends Model
{
    protected $table = 'programas_virtuales';

    protected $primaryKey = 'id_programa';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_plan',
        'id_propuesta',
        'estado',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan', 'id_plan');
    }

    public function categorias(): HasMany
    {
        return $this->hasMany(CategoriaVideo::class, 'id_programa', 'id_programa')->orderBy('orden');
    }
}
