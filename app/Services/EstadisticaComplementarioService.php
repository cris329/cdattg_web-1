<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AspiranteComplementario;
use App\Models\ComplementarioOfertado;
use App\Repositories\AspiranteComplementarioRepository;
use App\Repositories\ComplementarioOfertadoRepository;
use App\Repositories\PersonaRepository;

class EstadisticaComplementarioService
{
    public function __construct(
        private readonly AspiranteComplementarioRepository $aspiranteRepository,
        private readonly ComplementarioOfertadoRepository $programaRepository,
        private readonly PersonaRepository $personaRepository
    ) {}
    /**
     * Obtener estadísticas reales de la base de datos
     */
    public function obtenerEstadisticasReales(): array
    {
        $estadisticas = $this->aspiranteRepository->getEstadisticas();
        $totalAspirantes = $estadisticas['total'];
        $aspirantesAceptados = $estadisticas['aceptados'];
        
        $aspirantesPendientes = AspiranteComplementario::whereIn('estado', [1, 2])->count();
        $programasActivos = $this->programaRepository->countActivos();
        $tendenciaInscripciones = $this->aspiranteRepository->getTendenciaInscripciones(6);
        $distribucionProgramas = $this->aspiranteRepository->getDistribucionPorProgramas();

        $programasDemanda = $this->programaRepository->getProgramasConMayorDemanda(10)
            ->map(function($programa) {
                return [
                    'programa' => $programa->nombre,
                    'total_aspirantes' => $programa->total_aspirantes,
                    'aceptados' => $programa->aceptados,
                    'pendientes' => $programa->pendientes,
                    'tasa_aceptacion' => $programa->tasa_aceptacion
                ];
            });

        return [
            'total_aspirantes' => $totalAspirantes,
            'aspirantes_aceptados' => $aspirantesAceptados,
            'aspirantes_pendientes' => $aspirantesPendientes,
            'programas_activos' => $programasActivos,
            'tendencia_inscripciones' => $tendenciaInscripciones,
            'distribucion_programas' => $distribucionProgramas,
            'programas_demanda' => $programasDemanda
        ];
    }

    /**
     * Obtener estadísticas filtradas por criterios específicos
     */
    public function obtenerEstadisticasFiltradas(array $filtros): array
    {
        $query = AspiranteComplementario::with(['persona', 'complementario']);

        if (isset($filtros['fecha_inicio']) && isset($filtros['fecha_fin'])) {
            $query->whereBetween('created_at', [$filtros['fecha_inicio'], $filtros['fecha_fin']]);
        }

        if (isset($filtros['departamento_id'])) {
            $query->whereHas('persona', function($q) use ($filtros) {
                $q->where('departamento_id', $filtros['departamento_id']);
            });
        }

        if (isset($filtros['municipio_id'])) {
            $query->whereHas('persona', function($q) use ($filtros) {
                $q->where('municipio_id', $filtros['municipio_id']);
            });
        }

        if (isset($filtros['programa_id'])) {
            $query->where('complementario_id', $filtros['programa_id']);
        }

        return [
            'total_filtrado' => $query->count(),
            'aceptados_filtrado' => (clone $query)->where('estado', 3)->count(),
            'pendientes_filtrado' => (clone $query)->whereIn('estado', [1, 2])->count(),
            'datos' => $query->get()
        ];
    }

    /**
     * Generar reporte de tendencias mensuales
     */
    public function generarReporteTendencias(int $meses = 12)
    {
        return AspiranteComplementario::selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as total_inscripciones,
                SUM(CASE WHEN estado = 3 THEN 1 ELSE 0 END) as aceptados,
                SUM(CASE WHEN estado IN (1, 2) THEN 1 ELSE 0 END) as pendientes
            ')
            ->where('created_at', '>=', now()->subMonths($meses))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    /**
     * Obtener estadísticas por género
     */
    public function obtenerEstadisticasPorGenero()
    {
        return $this->personaRepository->getEstadisticasPorGenero();
    }

    /**
     * Obtener estadísticas por rango de edad
     */
    public function obtenerEstadisticasPorEdad()
    {
        return $this->personaRepository->getEstadisticasPorEdad();
    }
}
