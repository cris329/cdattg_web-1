<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait Seguimiento
{

    // Relación con el usuario que creó el registro
    public function userCreate() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_create_id');
    }

    // Relación con el usuario que actualizó el registro por última vez
    public function userUpdate() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_update_id');
    }

    // Alias de userCreate() para compatibilidad
    public function creador() : BelongsTo
    {
        return $this->userCreate();
    }

    // Alias de userUpdate() para compatibilidad
    public function actualizador() : BelongsTo
    {
        return $this->userUpdate();
    }
}
