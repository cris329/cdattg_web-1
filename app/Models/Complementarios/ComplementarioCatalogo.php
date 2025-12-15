<?php

namespace App\Models\Complementarios;

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
        'modalidad',
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
}


