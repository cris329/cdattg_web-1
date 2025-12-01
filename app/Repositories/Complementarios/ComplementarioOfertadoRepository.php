<?php

namespace App\Repositories\Complementarios;

use App\Models\Complementarios\ComplementarioOfertado;
use Illuminate\Database\Eloquent\Collection;

class ComplementarioOfertadoRepository
{
    /**
     * Obtener todos los programas con relaciones
     */
    public function getAll(array $relations = []): Collection
    {
        return ComplementarioOfertado::with($relations)->get();
    }

    /**
     * Obtener programas por estado
     */
    public function getByEstado(int $estado, array $relations = []): Collection
    {
        return ComplementarioOfertado::with($relations)
            ->where('estado', $estado)
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
        return ComplementarioOfertado::where('estado', 1)->count();
    }

    /**
     * Obtener estadísticas básicas de programas
     */
    public function getEstadisticas(): array
    {
        return [
            'total' => ComplementarioOfertado::count(),
            'activos' => $this->countActivos(),
            'sin_oferta' => ComplementarioOfertado::where('estado', 0)->count(),
            'cupos_llenos' => ComplementarioOfertado::where('estado', 2)->count(),
        ];
    }

    /**
     * Obtener programas con mayor demanda
     */
    public function getProgramasConMayorDemanda(int $limit = 10): Collection
    {
        return ComplementarioOfertado::selectRaw('
                complementarios_ofertados.id,
                complementarios_ofertados.codigo,
                complementarios_ofertados.nombre,
                complementarios_ofertados.duracion,
                complementarios_ofertados.cupos,
                complementarios_ofertados.estado,
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
                'complementarios_ofertados.estado',
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
            ->get()
            ->map(function($programa) {
                $tasaAceptacion = $programa->total_aspirantes > 0
                    ? round(($programa->aceptados / $programa->total_aspirantes) * 100, 1)
                    : 0;

                $programa->tasa_aceptacion = $tasaAceptacion;
                return $programa;
            });
    }
}
