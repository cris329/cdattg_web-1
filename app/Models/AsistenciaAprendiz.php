<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AsistenciaAprendiz extends Model
{
    use HasFactory;

    protected $table = 'asistencia_aprendices';

    protected $fillable = [
        'asistencia_id',
        'instructor_ficha_id',
        'aprendiz_ficha_id',
        'hora_ingreso',
        'hora_salida',
        'observaciones',
    ];

    protected $casts = [
        'hora_ingreso' => 'datetime',
        'hora_salida' => 'datetime',
    ];

    protected $dates = [
        'hora_ingreso',
        'hora_salida',
    ];

    /**
     * Relación con Asistencia
     * Un registro de asistencia de aprendiz pertenece a una asistencia
     */
    public function asistencia(): BelongsTo
    {
        return $this->belongsTo(Asistencia::class, 'asistencia_id');
    }

    /**
     * Relación con InstructorFichaCaracterizacion
     */
    public function instructorFichaCaracterizacion(): BelongsTo
    {
        return $this->belongsTo(InstructorFichaCaracterizacion::class, 'instructor_ficha_id');
    }

    /**
     * Relación con Aprendiz
     */
    public function aprendiz(): BelongsTo
    {
        return $this->belongsTo(Aprendiz::class, 'aprendiz_ficha_id');
    }

    /**
     * Relación con AprendizFicha (deprecated - usar aprendiz() en su lugar)
     * @deprecated Usar aprendiz() en su lugar
     */
    public function aprendizFicha(): BelongsTo
    {
        return $this->aprendiz();
    }

    /**
     * Scope para aprendices que han ingresado
     */
    public function scopeConIngreso(Builder $query): Builder
    {
        return $query->whereNotNull('hora_ingreso');
    }

    /**
     * Scope para aprendices que han salido
     */
    public function scopeConSalida(Builder $query): Builder
    {
        return $query->whereNotNull('hora_salida');
    }

    /**
     * Scope para aprendices que todavía están dentro
     */
    public function scopeAdentro(Builder $query): Builder
    {
        return $query->whereNotNull('hora_ingreso')
                    ->whereNull('hora_salida');
    }

    /**
     * Verificar si el aprendiz ha ingresado
     */
    public function haIngresado(): bool
    {
        return !is_null($this->hora_ingreso);
    }

    /**
     * Verificar si el aprendiz ha salido
     */
    public function haSalido(): bool
    {
        return !is_null($this->hora_salida);
    }

    /**
     * Verificar si el aprendiz está adentro
     */
    public function estaAdentro(): bool
    {
        return $this->haIngresado() && !$this->haSalido();
    }

    /**
     * Marcar ingreso del aprendiz
     */
    public function marcarIngreso(): bool
    {
        if ($this->haIngresado()) {
            return false; // Ya ha ingresado
        }

        $this->update(['hora_ingreso' => now()]);
        return true;
    }

    /**
     * Marcar salida del aprendiz
     */
    public function marcarSalida(): bool
    {
        if (!$this->haIngresado()) {
            return false; // No ha ingresado
        }

        if ($this->haSalido()) {
            return false; // Ya ha salido
        }

        $this->update(['hora_salida' => now()]);
        return true;
    }

    /**
     * Obtener tiempo total dentro en formato legible
     */
    public function getTiempoDentroAttribute(): string
    {
        if (!$this->haIngresado()) {
            return 'N/A';
        }

        $fin = $this->hora_salida ?? now();
        $duracion = $this->hora_ingreso->diff($fin);

        return $duracion->format('%H:%I:%S');
    }
}
