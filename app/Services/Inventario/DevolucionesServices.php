<?php

declare(strict_types=1);

namespace App\Services\Inventario;

use App\Models\Tema;
use App\Exceptions\OrdenException;

class DevolucionesServices
{
    private const THEME_ORDER_STATES = 'ESTADOS DE ORDEN';
    private const STATUS_APROBADA = 'APROBADA';

    /**
     * Obtiene estado APROBADA
     * Uso directo del modelo Tema/Parametro (clase externa, sin SOLID)
     *
     * @return \App\Models\Parametro
     * @throws OrdenException
     */
    public function obtenerEstadoAprobada()
    {
        $tema = Tema::where('name', self::THEME_ORDER_STATES)->first();
        if (!$tema) {
            throw new OrdenException("Tema 'ESTADOS DE ORDEN' no encontrado.");
        }

        $estado = $tema->parametros()
            ->where('name', self::STATUS_APROBADA)
            ->wherePivot('status', 1)
            ->first();

        if (!$estado) {
            throw new OrdenException("Estado 'APROBADA' no encontrado.");
        }

        return $estado;
    }
}

