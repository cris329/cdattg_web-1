<?php

namespace App\Repositories\Complementarios;

use App\Models\Complementarios\ComplementarioOfertado;
use Illuminate\Database\Eloquent\Collection;

class ComplementarioOfertadoRepository
{
    /**
     * Mapeo de valores legacy (0,1,2) a nombres de estado para búsqueda
     */
    private function getEstadoNombreByLegacyValue(int $estadoLegacy): string
    {
        return match ($estadoLegacy) {
            0 => 'Sin Oferta',
            1 => 'Con Oferta',
            2 => 'Cupos Llenos',
            default => 'Sin Oferta',
        };
    }

    /**
     * Obtener el estado_id correspondiente a un valor legacy
     */
    public function getEstadoIdByLegacyValue(int $estadoLegacy): ?int
    {
        $nombreEstado = $this->getEstadoNombreByLegacyValue($estadoLegacy);
        
        // Buscar el ParametroTema correspondiente al estado en el tema ESTADOS (ID 1)
        try {
            $temaEstado = \App\Models\Tema::find(1); // Tema "ESTADOS"
            
            if ($temaEstado) {
                // Buscar parámetro por nombre (los estados están en mayúsculas en la BD)
                $parametro = \App\Models\Parametro::where('name', strtoupper($nombreEstado))->first();
                
                if (!$parametro) {
                    // Intentar con el nombre exacto
                    $parametro = \App\Models\Parametro::where('name', $nombreEstado)->first();
                }
                
                if ($parametro) {
                    $parametroTema = \App\Models\ParametroTema::where('tema_id', $temaEstado->id)
                        ->where('parametro_id', $parametro->id)
                        ->first();
                    
                    if ($parametroTema) {
                        return $parametroTema->id;
                    }
                }
            }
        } catch (\Exception $e) {
            // Si hay error, retornar null
        }
        
        return null;
    }

    /**
     * Obtener todos los programas con relaciones
     */
    public function getAll(array $relations = []): Collection
    {
        return ComplementarioOfertado::with($relations)->get();
    }

    /**
     * Obtener programas por estado (compatibilidad con valores legacy 0,1,2)
     */
    public function getByEstado(int $estado, array $relations = []): Collection
    {
        $estadoId = $this->getEstadoIdByLegacyValue($estado);
        
        if (!$estadoId) {
            // Si no se encuentra el estado_id, retornar colección vacía
            return new Collection();
        }
        
        return ComplementarioOfertado::with($relations)
            ->where('estado_id', $estadoId)
            ->get();
    }

    /**
     * Obtener programas activos (estado = 1)
     */
    public function getActivos(array $relations = []): Collection
    {
        return $this->getByEstado(1, $relations);
    }

    /**
     * Obtener programa por ID con relaciones
     */
    public function findWithRelations(int $id, array $relations = []): ?ComplementarioOfertado
    {
        return ComplementarioOfertado::with($relations)->find($id);
    }

    /**
     * Buscar programa por nombre
     */
    public function findByNombre(string $nombre): ?ComplementarioOfertado
    {
        return ComplementarioOfertado::where('nombre', str_replace('-', ' ', $nombre))->first();
    }

    /**
     * Obtener programas con conteo de aspirantes
     */
    public function getAllWithAspirantesCount(array $relations = []): Collection
    {
        return ComplementarioOfertado::with($relations)
            ->withCount('aspirantes')
            ->get();
    }

    /**
     * Crear nuevo programa
     */
    public function create(array $data): ComplementarioOfertado
    {
        return ComplementarioOfertado::create($data);
    }

    /**
     * Actualizar programa
     */
    public function update(ComplementarioOfertado $programa, array $data): bool
    {
        return $programa->update($data);
    }

    /**
     * Eliminar programa
     */
    public function delete(ComplementarioOfertado $programa): bool
    {
        return $programa->delete();
    }

    /**
     * Contar programas activos
     */
    public function countActivos(): int
    {
        $estadoId = $this->getEstadoIdByLegacyValue(1);
        
        if (!$estadoId) {
            return 0;
        }
        
        return ComplementarioOfertado::where('estado_id', $estadoId)->count();
    }

    /**
     * Obtener estadísticas básicas de programas
     */
    public function getEstadisticas(): array
    {
        $sinOfertaId = $this->getEstadoIdByLegacyValue(0);
        $activosId = $this->getEstadoIdByLegacyValue(1);
        $cuposLlenosId = $this->getEstadoIdByLegacyValue(2);
        
        return [
            'total' => ComplementarioOfertado::count(),
            'activos' => $activosId ? ComplementarioOfertado::where('estado_id', $activosId)->count() : 0,
            'sin_oferta' => $sinOfertaId ? ComplementarioOfertado::where('estado_id', $sinOfertaId)->count() : 0,
            'cupos_llenos' => $cuposLlenosId ? ComplementarioOfertado::where('estado_id', $cuposLlenosId)->count() : 0,
        ];
    }

    /**
     * Obtener programas con mayor demanda
     * 
     * Nota: El cálculo de tasa_aceptacion ahora se realiza mediante un Accessor
     * en el modelo ComplementarioOfertado (getTasaAceptacionAttribute).
     */
    public function getProgramasConMayorDemanda(int $limit = 10): Collection
    {
        return ComplementarioOfertado::selectRaw('
                complementarios_ofertados.id,
                complementarios_ofertados.codigo,
                complementarios_ofertados.nombre,
                complementarios_ofertados.duracion,
                complementarios_ofertados.cupos,
                complementarios_ofertados.estado_id,
                complementarios_ofertados.modalidad_id,
                complementarios_ofertados.jornada_id,
                complementarios_ofertados.ambiente_id,
                complementarios_ofertados.justificacion,
                complementarios_ofertados.requisitos_ingreso,
                complementarios_ofertados.created_at,
                complementarios_ofertados.updated_at,
                COUNT(aspirantes_complementarios.id) as total_aspirantes,
                SUM(CASE WHEN aspirantes_complementarios.estado = 3 THEN 1 ELSE 0 END) as aceptados,
                SUM(CASE WHEN aspirantes_complementarios.estado = 1 THEN 1 ELSE 0 END) as pendientes
            ')
            ->leftJoin('aspirantes_complementarios', 'complementarios_ofertados.id', '=', 'aspirantes_complementarios.complementario_id')
            ->groupBy(
                'complementarios_ofertados.id',
                'complementarios_ofertados.codigo',
                'complementarios_ofertados.nombre',
                'complementarios_ofertados.duracion',
                'complementarios_ofertados.cupos',
                'complementarios_ofertados.estado_id',
                'complementarios_ofertados.modalidad_id',
                'complementarios_ofertados.jornada_id',
                'complementarios_ofertados.ambiente_id',
                'complementarios_ofertados.justificacion',
                'complementarios_ofertados.requisitos_ingreso',
                'complementarios_ofertados.created_at',
                'complementarios_ofertados.updated_at'
            )
            ->orderBy('total_aspirantes', 'desc')
            ->limit($limit)
            ->get();
    }
}
