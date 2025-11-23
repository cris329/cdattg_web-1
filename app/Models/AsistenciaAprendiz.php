<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaAprendiz extends Model
{
    use HasFactory;

    protected $table = 'asistencia_aprendices';

    protected $fillable = [
        'instructor_ficha_id',
        'aprendiz_ficha_id', // Ahora apunta directamente a aprendices.id
        'evidencia_id',
        'hora_ingreso',
        'hora_salida',
    ];

    /**
     * Relación con InstructorFichaCaracterizacion
     */
    public function instructorFichaCaracterizacion(): BelongsTo
    {
        return $this->belongsTo(InstructorFichaCaracterizacion::class, 'instructor_ficha_id');
    }

    /**
     * Relación con Aprendiz (aprendiz_ficha_id ahora apunta directamente a aprendices.id)
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
     * Relación con Evidencias
     */
    public function evidencia(): BelongsTo
    {
        return $this->belongsTo(Evidencias::class, 'evidencia_id');
    }
}
