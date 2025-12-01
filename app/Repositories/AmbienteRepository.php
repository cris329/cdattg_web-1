<?php

namespace App\Repositories;

use App\Core\Traits\HasCache;
use App\Models\Ambiente;
use Illuminate\Database\Eloquent\Collection;

class AmbienteRepository
{
    use HasCache;

    public function __construct()
    {
        $this->cacheType = 'parametros';
        $this->cacheTags = ['ambientes', 'infraestructura'];
    }

    /**
     * Obtiene todos los ambientes activos
     *
     * @return Collection
     */
    public function obtenerActivos(): Collection
    {
        return $this->cache('activos', function () {
            return Ambiente::where('status', true)
                ->with(['piso.bloque', 'sede'])
                ->orderBy('title')
                ->get();
        }, 720); // 12 horas
    }

    /**
     * Obtiene ambientes por sede
     *
     * @param int $sedeId
     * @return Collection
     */
    public function obtenerPorSede(int $sedeId): Collection
    {
        return $this->cache("sede_{$sedeId}", function () use ($sedeId) {
            return Ambiente::whereHas('piso.bloque', function ($query) use ($sedeId) {
                $query->where('sede_id', $sedeId);
            })
                ->with(['piso.bloque', 'piso.bloque.sede'])
                ->get();
        }, 720); // 12 horas
    }

    /**
     * Encuentra un ambiente con sus relaciones
     *
     * @param int $id
     * @return Ambiente|null
     */
    public function encontrar(int $id): ?Ambiente
    {
        return Ambiente::with(['piso.bloque', 'sede'])->find($id);
    }

    /**
     * Obtiene ambientes por piso
     *
     * @param int $pisoId
     * @return Collection
     */
    public function obtenerPorPiso(int $pisoId): Collection
    {
        return $this->cache("piso_{$pisoId}", function () use ($pisoId) {
            return Ambiente::where('piso_id', $pisoId)
                ->with(['piso.bloque', 'sede'])
                ->get();
        }, 720); // 12 horas
    }

    /**
     * Invalida caché
     *
     * @return void
     */
    public function invalidarCache(): void
    {
        $this->flushCache();
    }
}

