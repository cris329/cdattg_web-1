<?php

namespace App\Repositories\Complementarios;

use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Complementarios\ComplementarioOfertado;
use Illuminate\Database\Eloquent\Collection;

class AspiranteComplementarioRepository
{
    /**
     * Obtener aspirantes por programa con relaciones
     */
    public function findByPrograma(int $programaId, array $relations = ['persona', 'complementario']): Collection
    {
        return AspiranteComplementario::with($relations)
            ->where('complementario_id', $programaId)
            ->get();
    }

    /**
     * Obtener aspirantes con documentos por programa
     */
    public function findByProgramaConDocumentos(int $programaId): Collection
    {
        return AspiranteComplementario::with(['persona.tipoDocumento'])
            ->where('complementario_id', $programaId)
            ->whereHas('persona', function ($query) {
                $query->where('condocumento', 1);
            })
            ->get()
            ->sortBy(function ($aspirante) {
                return $aspirante->persona->numero_documento;
            });
    }

    /**
     * Obtener aspirantes con documentos excluyendo rechazados
     */
    public function findByProgramaConDocumentosExcluyendoRechazados(int $programaId): Collection
    {
        return AspiranteComplementario::with(['persona.tipoDocumento'])
            ->where('complementario_id', $programaId)
            ->where('estado', '!=', 2) // Excluir rechazados
            ->whereHas('persona', function ($query) {
                $query->where('condocumento', 1);
            })
            ->get()
            ->sortBy(function ($aspirante) {
                return $aspirante->persona->numero_documento;
            });
    }

    /**
     * Obtener aspirantes válidos para exportación (excluye rechazados, sin documento y no registrados en Sofia)
     */
    public function findByProgramaParaExportacion(int $programaId): Collection
    {
        return AspiranteComplementario::with(['persona.tipoDocumento', 'persona.parametroCaracterizacion'])
            ->where('complementario_id', $programaId)
            ->where('estado', '!=', 2) // Excluir rechazados
            ->whereHas('persona', function ($query) {
                $query->where('condocumento', 1)
                      ->where('estado_sofia', '!=', 0); // Excluir no registrados en SenasofiaPlus
            })
            ->get()
            ->sortBy(function ($aspirante) {
                return $aspirante->persona->numero_documento;
            });
    }

    /**
     * Obtener estadísticas de exclusión para un programa
     */
    public function getEstadisticasExclusion(int $programaId): array
    {
        $totalAspirantes = $this->countByPrograma($programaId);

        $rechazados = $this->countByEstado($programaId, 2);

        $sinDocumento = AspiranteComplementario::where('complementario_id', $programaId)
            ->where('estado', '!=', 2)
            ->whereHas('persona', function ($query) {
                $query->where('condocumento', 0);
            })
            ->count();

        $noRegistradosSofia = AspiranteComplementario::where('complementario_id', $programaId)
            ->where('estado', '!=', 2)
            ->whereHas('persona', function ($query) {
                $query->where('estado_sofia', 0);
            })
            ->count();

        $validos = AspiranteComplementario::where('complementario_id', $programaId)
            ->where('estado', '!=', 2)
            ->whereHas('persona', function ($query) {
                $query->where('condocumento', 1)
                      ->where('estado_sofia', '!=', 0);
            })
            ->count();

        return [
            'total' => $totalAspirantes,
            'rechazados' => $rechazados,
            'sin_documento' => $sinDocumento,
            'no_registrados_sofia' => $noRegistradosSofia,
            'validos' => $validos
        ];
    }

    /**
     * Contar aspirantes por estado en un programa
     */
    public function countByEstado(int $programaId, int $estado): int
    {
        return AspiranteComplementario::where('complementario_id', $programaId)
            ->where('estado', $estado)
            ->count();
    }

    /**
     * Contar total de aspirantes por programa
     */
    public function countByPrograma(int $programaId): int
    {
        return AspiranteComplementario::where('complementario_id', $programaId)->count();
    }

    /**
     * Verificar si una persona ya está inscrita en un programa
     */
    public function existeInscripcion(int $personaId, int $programaId): bool
    {
        return AspiranteComplementario::where('persona_id', $personaId)
            ->where('complementario_id', $programaId)
            ->exists();
    }

    /**
     * Buscar aspirante específico en un programa
     */
    public function findByPersonaYPrograma(int $personaId, int $programaId): ?AspiranteComplementario
    {
        return AspiranteComplementario::where('persona_id', $personaId)
            ->where('complementario_id', $programaId)
            ->first();
    }

    /**
     * Buscar aspirante por ID
     */
    public function findById(int $id): ?AspiranteComplementario
    {
        return AspiranteComplementario::find($id);
    }

    /**
     * Crear nuevo aspirante
     */
    public function create(array $data): AspiranteComplementario
    {
        return AspiranteComplementario::create($data);
    }

    /**
     * Actualizar aspirante
     */
    public function update(AspiranteComplementario $aspirante, array $data): bool
    {
        return $aspirante->update($data);
    }

    /**
     * Eliminar aspirante (soft delete o cambio de estado)
     */
    public function delete(AspiranteComplementario $aspirante): bool
    {
        // Cambiar estado a rechazado en lugar de eliminar
        return $aspirante->update(['estado' => 2]);
    }

    /**
     * Obtener aspirantes para exportación con caracterización
     */
    public function findForExport(int $programaId): Collection
    {
        return AspiranteComplementario::with(['persona.caracterizacion', 'persona.tipoDocumento'])
            ->where('complementario_id', $programaId)
            ->get();
    }

    /**
     * Obtener estadísticas básicas de aspirantes
     */
    public function getEstadisticas(): array
    {
        return [
            'total' => AspiranteComplementario::count(),
            'activos' => AspiranteComplementario::where('estado', 1)->count(),
            'aceptados' => AspiranteComplementario::where('estado', 3)->count(),
            'rechazados' => AspiranteComplementario::where('estado', 2)->count(),
        ];
    }

    /**
     * Obtener tendencia de inscripciones por mes
     */
    public function getTendenciaInscripciones(int $meses = 6): Collection
    {
        $isSqlite = \Illuminate\Support\Facades\DB::getDriverName() === 'sqlite';

        if ($isSqlite) {
            return AspiranteComplementario::selectRaw('
                    CAST(strftime("%Y", created_at) AS INTEGER) as year,
                    CAST(strftime("%m", created_at) AS INTEGER) as month,
                    COUNT(*) as total
                ')
                ->where('created_at', '>=', now()->subMonths($meses))
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
        }

        return AspiranteComplementario::selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as total
            ')
            ->where('created_at', '>=', now()->subMonths($meses))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
    }

    /**
     * Obtener distribución por programas
     */
    public function getDistribucionPorProgramas(): Collection
    {
        return AspiranteComplementario::selectRaw('
                complementarios_ofertados.nombre as programa,
                COUNT(*) as total
            ')
            ->join('complementarios_ofertados', 'aspirantes_complementarios.complementario_id', '=', 'complementarios_ofertados.id')
            ->groupBy('complementarios_ofertados.nombre')
            ->orderBy('total', 'desc')
            ->get();
    }
}
