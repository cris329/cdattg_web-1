<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GuiasAprendizaje;
use App\Models\User;

class ResultadosAprendizaje extends Model
{
    /** @use HasFactory<\Database\Factories\ResultadosAprendizajeFactory> */
    use HasFactory;
    protected $table = 'resultados_aprendizajes';
    protected $fillable = [
        'codigo',
        'nombre',
        'duracion',
        'status',
        'user_create_id',
        'user_edit_id',
    ];

    protected $casts = [
        'duracion' => 'decimal:2',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación muchos a muchos con GuiasAprendizaje a través de la tabla intermedia
     */
    public function guiasAprendizaje()
    {
        return $this->belongsToMany(GuiasAprendizaje::class, 'guia_aprendizaje_rap', 'rap_id', 'guia_aprendizaje_id')
                    ->withPivot('user_create_id', 'user_edit_id')
                    ->withTimestamps();
    }

    /**
     * Relación con la tabla intermedia
     */
    public function guiaAprendizajeRap()
    {
        return $this->hasMany(GuiaAprendizajeRap::class, 'rap_id');
    }

    /**
     * Relación muchos a muchos con Competencia a través de la tabla intermedia
     */
    public function competencias()
    {
        return $this->belongsToMany(Competencia::class, 'resultados_aprendizaje_competencia', 'rap_id', 'competencia_id')
                    ->withPivot('duracion', 'user_create_id', 'user_edit_id')
                    ->withTimestamps();
    }

    public function asignacionesInstructor()
    {
        return $this->belongsToMany(
            AsignacionInstructor::class,
            'asignacion_instructor_resultado',
            'resultado_aprendizaje_id',
            'asignacion_id'
        )->withTimestamps();
    }

    /**
     * Relación con la tabla intermedia resultados_aprendizaje_competencia
     */
    public function resultadosCompetencia()
    {
        return $this->hasMany(ResultadosCompetencia::class, 'rap_id');
    }

    /**
     * Relación con el usuario que creó el resultado
     */
    public function userCreate()
    {
        return $this->belongsTo(User::class, 'user_create_id');
    }

    /**
     * Relación con el usuario que editó el resultado
     */
    public function userEdit()
    {
        return $this->belongsTo(User::class, 'user_edit_id');
    }

    /**
     * SCOPE: Filtrar resultados activos
     */
    public function scopeActivos($query)
    {
        return $query->where('status', 1);
    }

    /**
     * SCOPE: Filtrar resultados inactivos
     */
    public function scopeInactivos($query)
    {
        return $query->where('status', 0);
    }

    /**
     * SCOPE: Filtrar por competencia
     */
    public function scopePorCompetencia($query, $competenciaId)
    {
        return $query->whereHas('competencias', function($q) use ($competenciaId) {
            $q->where('competencias.id', $competenciaId);
        });
    }

    /**
     * SCOPE: Filtrar por código
     */
    public function scopePorCodigo($query, $codigo)
    {
        return $query->where('codigo', 'LIKE', "%{$codigo}%");
    }


    /**
     * SCOPE: Ordenar por código ascendente
     */
    public function scopeOrdenadoPorCodigo($query)
    {
        return $query->orderBy('codigo', 'asc');
    }

    /**
     * MÉTODO HELPER: Verificar si el resultado está activo
     */
    public function isActivo(): bool
    {
        return $this->status == 1;
    }


    /**
     * MÉTODO HELPER: Verificar si está vigente
     * Siempre retorna true ya que no hay fechas de vigencia
     */
    public function estaVigente(): bool
    {
        return true;
    }

    /**
     * MÉTODO HELPER: Contar guías asociadas
     */
    public function contarGuiasAsociadas(): int
    {
        return $this->guiasAprendizaje()->count();
    }

    /**
     * MÉTODO HELPER: Obtener estado formateado
     */
    public function getEstadoFormateadoAttribute(): string
    {
        return $this->status == 1 ? 'ACTIVO' : 'INACTIVO';
    }

    /**
     * MÉTODO HELPER: Obtener nombre completo con código
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->codigo} - {$this->nombre}";
    }
}
