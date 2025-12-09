<?php

declare(strict_types=1);

namespace App\Inventario\Repositories\ParametroTema;

use App\Inventario\Interfaces\Repositories\ParametroTema\ParametroTemaRepositoryInterface;
use App\Models\Tema;
use App\Models\Parametro;
use App\Models\ParametroTema;
use Illuminate\Support\Collection;

class ParametroTemaRepository implements ParametroTemaRepositoryInterface
{
    /**
     * Obtiene parámetros tema por nombre de tema
     *
     * @param string $nombreTema
     * @return Collection
     */
    public function obtenerPorTema(string $nombreTema): Collection
    {
        $tema = Tema::where('name', $nombreTema)->first();

        if (!$tema) {
            return collect([]);
        }

        return ParametroTema::with('parametro')
            ->where('tema_id', $tema->id)
            ->where('status', 1)
            ->get();
    }

    /**
     * Obtiene un parámetro tema específico por tema y parámetro
     *
     * @param int $temaId
     * @param int $parametroId
     * @return ParametroTema|null
     */
    public function obtenerPorTemaYParametro(int $temaId, int $parametroId)
    {
        return ParametroTema::where('tema_id', $temaId)
            ->where('parametro_id', $parametroId)
            ->where('status', 1)
            ->first();
    }

    /**
     * Obtiene un estado específico por nombre
     *
     * @param string $nombreEstado
     * @param string $nombreTema
     * @return ParametroTema|null
     */
    public function obtenerEstadoPorNombre(string $nombreEstado, string $nombreTema)
    {
        $tema = Tema::where('name', $nombreTema)->first();

        if (!$tema) {
            return null;
        }

        $parametro = Parametro::where('name', $nombreEstado)->first();
        
        if (!$parametro) {
            return null;
        }

        return ParametroTema::where('tema_id', $tema->id)
            ->where('parametro_id', $parametro->id)
            ->where('status', 1)
            ->first();
    }
}
