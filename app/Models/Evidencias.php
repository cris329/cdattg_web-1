<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evidencias extends Model
{
    protected $table = 'evidencias';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'id_estado',
        'fecha_evidencia',
        'user_create_id',
        'user_edit_id',
    ];

    protected $casts = [
        'fecha_evidencia' => 'date',
    ];

    protected $dates = [
        'fecha_evidencia',
    ];

    /**
     * Relación con las asistencias
     * Una evidencia puede tener muchas asistencias (sesiones)
     */
    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'evidencia_id');
    }

    /**
     * Relación con asistencias activas
     */
    public function asistenciasActivas(): HasMany
    {
        return $this->asistencias()->activa();
    }

    /**
     * Relación con asistencias finalizadas
     */
    public function asistenciasFinalizadas(): HasMany
    {
        return $this->asistencias()->finalizada();
    }

    /**
     * Relación con ambiente (si existe)
     */
    public function ambiente()
    {
        return $this->belongsTo(Ambientes::class, 'id_ambiente');
    }

    /**
     * Relación muchos a muchos con GuiasAprendizaje a través de la tabla intermedia
     */
    public function guiasAprendizaje()
    {
        return $this->belongsToMany(GuiasAprendizaje::class, 'evidencia_guia_aprendizaje', 'evidencia_id', 'guia_aprendizaje_id')
                    ->withPivot('user_create_id', 'user_edit_id')
                    ->withTimestamps();
    }

    /**
     * Relación con el usuario que creó el registro
     */
    public function userCreate()
    {
        return $this->belongsTo(User::class, 'user_create_id');
    }

    /**
     * Relación con el usuario que editó el registro
     */
    public function userEdit()
    {
        return $this->belongsTo(User::class, 'user_edit_id');
    }

    /**
     * Verificar si tiene asistencias activas
     */
    public function tieneAsistenciasActivas(): bool
    {
        return $this->asistenciasActivas()->exists();
    }

    /**
     * Obtener el número total de asistencias
     */
    public function getNumeroAsistenciasAttribute(): int
    {
        return $this->asistencias()->count();
    }

    /**
     * Obtener el número de asistencias activas
     */
    public function getNumeroAsistenciasActivasAttribute(): int
    {
        return $this->asistenciasActivas()->count();
    }

    /**
     * Obtener el número de asistencias finalizadas
     */
    public function getNumeroAsistenciasFinalizadasAttribute(): int
    {
        return $this->asistenciasFinalizadas()->count();
    }

    /**
     * Método estático para terminar actividad (legacy)
     */
    public static function terminarActividad($id)
    {
        $actividad = Evidencias::find($id);
        if ($actividad) {
            $actividad->id_estado = 27;
            $actividad->save();
        }
    }

    /**
     * Scope para evidencias activas
     */
    public function scopeActiva($query)
    {
        return $query->where('id_estado', 1);
    }

    /**
     * Scope para evidencias inactivas
     */
    public function scopeInactiva($query)
    {
        return $query->where('id_estado', '!=', 1);
    }
}
