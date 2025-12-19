<?php

namespace App\Models\Complementarios;

use App\Models\ParametroTema;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplementarioCatalogo extends Model
{
    use HasFactory;

    protected $table = 'complementarios_catalogo';

    protected $fillable = [
        'prf_codigo',
        'version',
        'cod_ver',
        'denominacion',
        'nivel_formacion',
        'duracion_horas',
        'requisitos_ingreso',
        'linea_tecnologica',
        'red_tecnologica',
        'red_conocimiento',
        'modalidad_id',
        'apuesta_prioritaria',
        'tipo_permiso',
        'multiple_inscripcion',
        'alamedida',
        'fic',
        'creditos',
        'indice',
        'ocupacion',
        'activo',
    ];

    protected $casts = [
        'multiple_inscripcion' => 'boolean',
        'alamedida' => 'boolean',
        'fic' => 'boolean',
        'activo' => 'boolean',
        'version' => 'integer',
        'duracion_horas' => 'integer',
        'creditos' => 'integer',
    ];

    /**
     * Relación con la modalidad de formación (ParametroTema)
     */
    public function modalidad()
    {
        return $this->belongsTo(ParametroTema::class, 'modalidad_id');
    }

    /**
     * Accessor para mantener compatibilidad hacia atrás con código que usa modalidad (string)
     * Devuelve el nombre del parámetro de modalidad
     */
    public function getModalidadAttribute(): ?string
    {
        if ($this->modalidad_id && $this->relationLoaded('modalidad')) {
            return $this->modalidad?->parametro?->name;
        }
        
        if ($this->modalidad_id) {
            $this->loadMissing(['modalidad.parametro']);
            return $this->modalidad?->parametro?->name;
        }
        
        return null;
    }
}


