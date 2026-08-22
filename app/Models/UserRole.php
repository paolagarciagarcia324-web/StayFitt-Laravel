<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRole extends Model
{
    protected $table = 'user_roles';

    protected $primaryKey = 'id_user_rol';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_user',
        'id_rol',
        'asignado_por',
        'asignado_en'
    ];
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }
    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por', 'id_user');
    }
}
