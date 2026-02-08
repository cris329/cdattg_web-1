<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';

    protected $fillable = [
        'evidencia_id',
        'instructor_ficha_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'is_finished',
        'user_create_id',
        'user_edit_id',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime',
        'is_finished' => 'boolean',
    ];

    protected $dates = [
        'fecha',
        'hora_inicio',
        'hora_fin',
    ];

    /**
     * Relación con la evidencia
     * Una asistencia pertenece a una evidencia
     */
    public function evidencia()
    {
        return $this->belongsTo(Evidencias::class, 'evidencia_id');
    }

    /**
     * Relación con la ficha del instructor
     * Una asistencia pertenece a una ficha de instructor
     */
    public function instructorFicha()
    {
        return $this->belongsTo(FichaCaracterizacion::class, 'instructor_ficha_id');
    }

    /**
     * Relación con los aprendices de esta asistencia
     * Una asistencia tiene muchos registros de asistencia de aprendices
     */
    public function asistenciaAprendices()
    {
        return $this->hasMany(AsistenciaAprendiz::class, 'asistencia_id');
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
     * Scope para asistencias activas (no finalizadas)
     */
    public function scopeActiva(Builder $query): Builder
    {
        return $query->where('is_finished', false);
    }

    /**
     * Scope para asistencias finalizadas
     */
    public function scopeFinalizada(Builder $query): Builder
    {
        return $query->where('is_finished', true);
    }

    /**
     * Scope para asistencias de una ficha específica
     */
    public function scopeDeFicha(Builder $query, int $fichaId): Builder
    {
        return $query->where('instructor_ficha_id', $fichaId);
    }

    /**
     * Scope para asistencias de una evidencia específica
     */
    public function scopeDeEvidencia(Builder $query, int $evidenciaId): Builder
    {
        return $query->where('evidencia_id', $evidenciaId);
    }

    /**
     * Scope para asistencias de hoy
     */
    public function scopeHoy(Builder $query): Builder
    {
        return $query->whereDate('fecha', today());
    }

    /**
     * Verificar si la asistencia está activa
     */
    public function estaActiva(): bool
    {
        return !$this->is_finished;
    }

    /**
     * Verificar si la asistencia está finalizada
     */
    public function estaFinalizada(): bool
    {
        return $this->is_finished;
    }

    /**
     * Finalizar la asistencia
     */
    public function finalizar(): bool
    {
        if ($this->is_finished) {
            return false; // Ya está finalizada
        }

        $this->update([
            'is_finished' => true,
            'hora_fin' => now(),
            'user_edit_id' => auth()->id(),
        ]);

        return true;
    }

    /**
     * Obtener la duración de la asistencia en formato legible
     */
    public function getDuracionAttribute(): string
    {
        if (!$this->hora_inicio) {
            return 'N/A';
        }

        $fin = $this->hora_fin ?? now();
        $duracion = $this->hora_inicio->diff($fin);

        return $duracion->format('%H:%I:%S');
    }

    /**
     * Obtener el número de aprendices registrados
     */
    public function getNumeroAprendicesAttribute(): int
    {
        return $this->asistenciaAprendices()->count();
    }

    /**
     * Validar que no exista una asistencia activa para la misma ficha
     */
    public static function validarAsistenciaUnica(int $fichaId): ?self
    {
        return self::deFicha($fichaId)
            ->activa()
            ->first();
    }

    /**
     * Crear nueva asistencia con validaciones
     */
    public static function crearNueva(array $datos): self
    {
        // Validar que no exista asistencia activa
        $asistenciaActiva = self::validarAsistenciaUnica($datos['instructor_ficha_id']);
        
        if ($asistenciaActiva) {
            throw new \Exception('Ya existe una asistencia activa para esta ficha. Finalice la asistencia actual antes de crear una nueva.');
        }

        return self::create(array_merge($datos, [
            'is_finished' => false,
            'user_create_id' => auth()->id(),
            'user_edit_id' => auth()->id(),
        ]));
    }
}
