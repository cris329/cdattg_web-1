<?php

namespace App\Models\Inventario;

use Database\Factories\Inventario\NotificacionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;

class Notificacion extends DatabaseNotification
{
    use HasFactory;
    protected $table = 'notificaciones';

    public $timestamps = true;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tipo',
        'datos',
        'leida_en',
        'notificable_type',
        'notificable_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Override para usar nuestros nombres de columna personalizados
     */
    public function getNotifiableTypeAttribute(): ?string
    {
        return $this->attributes['notificable_type'] ?? null;
    }

    /**
     * Override para usar nuestros nombres de columna personalizados
     */
    public function getNotifiableIdAttribute(): int|string|null
    {
        return $this->attributes['notificable_id'] ?? null;
    }

    protected $casts = [
        'datos' => 'array',
        'leida_en' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Laravel espera 'data' pero nuestra columna es 'datos'
     */
    public function getDataAttribute() : array
    {
        $datos = $this->attributes['datos'] ?? null;

        if ($datos === null) {
            return [];
        }

        if (is_array($datos)) {
            return $datos;
        }

        // Si es JSON string, decodificarlo
        $decoded = is_string($datos) ? json_decode($datos, true) : null;
        return is_array($decoded) ? $decoded : [];
    }

  
    public function getTypeAttribute() : ?string
    {
        return $this->attributes['tipo'] ?? null;
    }

    public function getReadAtAttribute() : ?Carbon
    {
        return $this->leida_en;
    }

    /**
     * Marcar la notificación como leída
     */
    public function markAsRead() : void
    {
        if (is_null($this->leida_en)) {
            $this->forceFill(['leida_en' => $this->freshTimestamp()])->save();
        }
    }

    /**
     * Marcar la notificación como no leída
     */
    public function markAsUnread() : void
    {
        if (!is_null($this->leida_en)) {
            $this->forceFill(['leida_en' => null])->save();
        }
    }

    /**
     * Determinar si la notificación ha sido leída
     */
    public function read() : bool
    {
        return $this->leida_en !== null;
    }

    /**
     * Determinar si la notificación no ha sido leída
     */
    public function unread() : bool
    {
        return $this->leida_en === null;
    }
}
