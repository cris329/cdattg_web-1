<?php

namespace App\Repositories;

use App\Models\ResultadosAprendizaje;
use Carbon\Carbon;

class ResultadosAprendizajeRepository
{
    /**
     * Obtiene los resultados de aprendizaje activos (vigentes)
     * Ya no se usan fechas, solo se verifica el estado
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getResultadosVigentes()
    {
        return ResultadosAprendizaje::where('status', 1)->get();
    }

    
    /**
     * Obtiene los resultados de aprendizaje de una competencia
     *
     * @param int $competenciaId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getResultadosAprendizajePorCompetencia($competenciaId)
    {
        return ResultadosAprendizaje::whereHas('competencia', function($query) use ($competenciaId) {
            $query->where('competencias.id', $competenciaId);
        })->get();
    }
    

    /**
     * Obtiene los resultados de aprendizaje por ID de guía de aprendizaje
     * que están activos (vigentes)
     *
     * @param int $guiaAprendizajeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getResultadosVigentesPorGuia($guiaAprendizajeId)
    {
        return ResultadosAprendizaje::whereHas('guiasAprendizaje', function($query) use ($guiaAprendizajeId) {
            $query->where('guia_aprendizaje_id', $guiaAprendizajeId);
        })->where('status', 1)->get();
    }
}