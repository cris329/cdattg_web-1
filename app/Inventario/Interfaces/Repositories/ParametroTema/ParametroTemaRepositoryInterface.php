<?php

declare(strict_types=1);

namespace App\Inventario\Interfaces\Repositories\ParametroTema;

use Illuminate\Support\Collection;

interface ParametroTemaRepositoryInterface
{
    /**
     * Obtiene parámetros tema por nombre de tema
     *
     * @param string $nombreTema
     * @return Collection
     */
    public function obtenerPorTema(string $nombreTema): Collection;

    /**
     * Obtiene un parámetro tema específico por tema y parámetro
     *
     * @param int $temaId
     * @param int $parametroId
     * @return \App\Models\ParametroTema|null
     */
    public function obtenerPorTemaYParametro(int $temaId, int $parametroId);

    /**
     * Obtiene un estado específico por nombre
     *
     * @param string $nombreEstado
     * @param string $nombreTema
     * @return \App\Models\ParametroTema|null
     */
    public function obtenerEstadoPorNombre(string $nombreEstado, string $nombreTema);
}
