<?php

namespace App\Repositories\Complementarios;

use App\Models\Complementarios\SenasofiaplusValidationLog;

class SenasofiaplusValidationLogRepository
{
    /**
     * Registra un log de validación
     *
     * @param array $datos
     * @return SenasofiaplusValidationLog
     */
    public function registrar(array $datos): SenasofiaplusValidationLog
    {
        return SenasofiaplusValidationLog::crearLog($datos);
    }

    /**
     * Obtiene logs por aspirante
     *
     * @param int $aspiranteId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerLogsPorAspirante(int $aspiranteId, int $limit = 50)
    {
        return SenasofiaplusValidationLog::getLogsPorAspirante($aspiranteId, $limit);
    }

    /**
     * Obtiene estadísticas de validaciones
     *
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return array
     */
    public function obtenerEstadisticas(string $fechaInicio, string $fechaFin): array
    {
        return SenasofiaplusValidationLog::getEstadisticasValidaciones($fechaInicio, $fechaFin);
    }

    /**
     * Obtiene auditoría completa de validaciones
     *
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerAuditoria(string $fechaInicio, string $fechaFin)
    {
        return SenasofiaplusValidationLog::whereBetween('fecha_accion', [$fechaInicio, $fechaFin])
            ->with(['aspirante.persona', 'user'])
            ->orderBy('fecha_accion', 'desc')
            ->get();
    }

    /**
     * Obtiene logs con errores
     *
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerLogsConErrores(string $fechaInicio, string $fechaFin)
    {
        return SenasofiaplusValidationLog::whereBetween('fecha_accion', [$fechaInicio, $fechaFin])
            ->where('resultado', 'error')
            ->with(['aspirante.persona', 'user'])
            ->orderBy('fecha_accion', 'desc')
            ->get();
    }
}
