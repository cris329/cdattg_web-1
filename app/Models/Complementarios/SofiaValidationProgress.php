<?php

namespace App\Models\Complementarios;

use App\Models\User;
use Database\Factories\SofiaValidationProgressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SofiaValidationProgress extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return SofiaValidationProgressFactory::new();
    }

    protected $table = 'sofia_validation_progress';

    protected $fillable = [
        'complementario_id',
        'user_id',
        'status',
        'total_aspirantes',
        'processed_aspirantes',
        'successful_validations',
        'failed_validations',
        'errors',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'errors' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Relación con el programa complementario
     */
    public function complementario()
    {
        return $this->belongsTo(ComplementarioOfertado::class, 'complementario_id');
    }

    /**
     * Relación con el usuario que inició la validación
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calcular el porcentaje de progreso
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->total_aspirantes === 0) {
            return 0;
        }

        return round(($this->processed_aspirantes / $this->total_aspirantes) * 100, 2);
    }

    /**
     * Obtener el estado legible
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            284 => 'Pendiente', // PENDING
            285 => 'Procesando', // PROCESSING
            286 => 'Completado', // COMPLETED
            287 => 'Fallido', // FAILED
            default => 'Desconocido'
        };
    }

    /**
     * Marcar como iniciado
     */
    public function markAsStarted()
    {
        $this->update([
            'status' => 285, // PROCESSING = 285 según ParametroSeeder
            'started_at' => now(),
        ]);
    }

    /**
     * Marcar como completado
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 286, // COMPLETED = 286 según ParametroSeeder
            'completed_at' => now(),
        ]);
    }

    /**
     * Marcar como fallido
     */
    public function markAsFailed($errors = [])
    {
        $this->update([
            'status' => 287, // FAILED = 287 según ParametroSeeder
            'errors' => $errors,
            'completed_at' => now(),
        ]);
    }

    /**
     * Incrementar contador de procesados
     */
    public function incrementProcessed($successful = true)
    {
        $this->increment('processed_aspirantes');

        if ($successful) {
            $this->increment('successful_validations');
        } else {
            $this->increment('failed_validations');
        }
    }
}
