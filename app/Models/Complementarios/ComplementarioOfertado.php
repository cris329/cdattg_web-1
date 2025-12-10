<?php

namespace App\Models\Complementarios;

use App\Models\Ambiente;
use App\Models\Competencia;
use App\Models\GuiasAprendizaje;
use App\Models\JornadaFormacion;
use App\Models\Parametro;
use App\Models\ParametroTema;
use App\Models\ResultadosAprendizaje;
use App\Models\User;
use Database\Factories\ComplementarioOfertadoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplementarioOfertado extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ComplementarioOfertadoFactory::new();
    }

    protected $table = 'complementarios_ofertados';

    protected $fillable = [
        'codigo',
        'nombre',
        'justificacion',
        'requisitos_ingreso',
        'duracion',
        'cupos',
        'estado_id',
        'modalidad_id',
        'jornada_id',
        'ambiente_id',
    ];

    /**
     * Valores por defecto para los atributos
     */
    protected $attributes = [
        'estado_id' => 3, // ID de parametros_temas para "Sin Oferta" (parametro_id = 277)
    ];

    public function modalidad()
    {
        return $this->belongsTo(ParametroTema::class, 'modalidad_id');
    }

    public function jornada()
    {
        return $this->belongsTo(JornadaFormacion::class, 'jornada_id');
    }

    public function ambiente()
    {
        return $this->belongsTo(Ambiente::class, 'ambiente_id');
    }

    /**
     * Relación con el estado parametrizado del programa complementario
     */
    public function estado()
    {
        return $this->belongsTo(ParametroTema::class, 'estado_id');
    }

    public function diasFormacion()
    {
        return $this->belongsToMany(Parametro::class, 'complementarios_ofertados_dias_formacion', 'complementario_id', 'dia_id')
                    ->withPivot('hora_inicio', 'hora_fin');
    }

    public function aspirantes()
    {
        return $this->hasMany(AspiranteComplementario::class, 'complementario_id');
    }

    /**
     * Relación muchos a muchos con Competencias
     */
    public function competencias()
    {
        return $this->belongsToMany(
            Competencia::class,
            'competencia_complementario',
            'complementario_id',
            'competencia_id'
        )->withTimestamps()
         ->withPivot('user_create_id', 'user_edit_id');
    }

    /**
     * Relación muchos a muchos con Resultados de Aprendizaje
     */
    public function raps()
    {
        return $this->belongsToMany(
            ResultadosAprendizaje::class,
            'resultado_aprendizaje_complementario',
            'complementario_id',
            'rap_id'
        )->withTimestamps()
         ->withPivot('user_create_id', 'user_edit_id');
    }

    /**
     * Relación muchos a muchos con Guías de Aprendizaje
     */
    public function guiasAprendizaje()
    {
        return $this->belongsToMany(
            GuiasAprendizaje::class,
            'guia_aprendizaje_complementario',
            'complementario_id',
            'guia_aprendizaje_id'
        )->withTimestamps()
         ->withPivot('user_create_id', 'user_edit_id');
    }

    /**
     * Accessor para compatibilidad hacia atrás con código que espera el campo 'estado'
     * Devuelve el valor numérico legacy basado en el nombre del parámetro
     * Nota: Para evitar referencia circular, no usamos $this->estado
     */
    public function getEstadoAttribute()
    {
        // Obtener el estado_id directamente del atributo
        $estadoId = $this->attributes['estado_id'] ?? null;

        if (!$estadoId) {
            return 0; // Valor por defecto: Sin Oferta
        }

        // Intentar obtener el nombre del parámetro a través de la relación
        try {
            // Usar una consulta directa para evitar referencia circular
            $parametroTema = \App\Models\ParametroTema::with('parametro')->find($estadoId);
            if ($parametroTema && $parametroTema->parametro) {
                $nombre = strtoupper(trim($parametroTema->parametro->name));

                return match ($nombre) {
                    'SIN OFERTA' => 0,
                    'CON OFERTA' => 1,
                    'CUPOS LLENOS' => 2,
                    default => 0,
                };
            }
        } catch (\Exception $e) {
            // Si hay error, retornar valor por defecto
        }

        return 0;
    }

    public function getEstadoLabelAttribute()
    {
        // Obtener el estado_id directamente del atributo
        $estadoId = $this->attributes['estado_id'] ?? null;

        if (!$estadoId) {
            return 'Desconocido';
        }

        $label = 'Desconocido';

        // Intentar obtener el nombre del parámetro a través de la relación
        try {
            /** @var ParametroTema|null $estadoRelacion */
            $estadoRelacion = null;

            if ($this->relationLoaded('estado')) {
                $estadoRelacion = $this->getRelation('estado');
            }

            if ($estadoRelacion instanceof ParametroTema) {
                if (!$estadoRelacion->relationLoaded('parametro')) {
                    $estadoRelacion->load('parametro');
                }
                if ($this->estado->parametro) {
                    $label = $this->estado->parametro->name;
                }
            } else {
                // Si la relación no está cargada, hacer una consulta
                $estado = $this->estado()->with('parametro')->first();
                if ($estado && $estado->parametro) {
                    $label = $estado->parametro->name;
                }
            }
        } catch (\Exception $e) {
            // Si hay error, mantener el valor por defecto
        }

        return $label;
    }

    public function getBadgeClassAttribute()
    {
        $estadoNombre = $this->estado_label;

        return match ($estadoNombre) {
            'SIN OFERTA' => 'bg-success',
            'CON OFERTA' => 'bg-warning',
            'CUPOS LLENOS' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getIconoAttribute()
    {
        $iconos = [
            'Auxiliar de Cocina' => 'fas fa-utensils',
            'Acabados en Madera' => 'fas fa-hammer',
            'Confección de Prendas' => 'fas fa-cut',
            'Mecánica Básica Automotriz' => 'fas fa-car',
            'Cultivos de Huertas Urbanas' => 'fas fa-spa',
            'Normatividad Laboral' => 'fas fa-gavel',
        ];

        return $iconos[$this->nombre] ?? 'fas fa-graduation-cap';
    }

    /**
     * Calcular tasa de aceptación de aspirantes
     *
     * Este accessor calcula la tasa de aceptación basándose en los atributos
     * total_aspirantes y aceptados que pueden venir de consultas agregadas.
     *
     * @return float Tasa de aceptación en porcentaje (0-100)
     */
    public function getTasaAceptacionAttribute(): float
    {
        // Los atributos agregados se almacenan directamente en $this->attributes
        $totalAspirantes = $this->attributes['total_aspirantes'] ?? 0;
        $aceptados = $this->attributes['aceptados'] ?? 0;

        if ($totalAspirantes > 0 && is_numeric($aceptados) && is_numeric($totalAspirantes)) {
            return round(($aceptados / $totalAspirantes) * 100, 1);
        }

        return 0.0;
    }
}
