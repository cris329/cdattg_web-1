<?php

declare(strict_types=1);

namespace App\Inventario\Services\Devolucion;

use App\Models\Inventario\Devolucion;
use App\Inventario\Interfaces\Services\TransactionServiceInterface;
use App\Models\Tema;
use App\Exceptions\DevolucionException;

/**
 * Servicio para gestión de devoluciones
 * Cumple SRP: responsabilidad única de gestionar devoluciones
 */
class DevolucionService
{
    protected TransactionServiceInterface $transactionService;

    public function __construct(
        TransactionServiceInterface $transactionService
    ) {
        $this->transactionService = $transactionService;
    }

    /**
     * Registra una devolución y construye mensaje de respuesta
     *
     * @param int $detalleOrdenId
     * @param int $cantidadDevuelta
     * @param string|null $observaciones
     * @return array ['devolucion' => Devolucion, 'mensaje' => string]
     * @throws DevolucionException
     */
    public function registrarDevolucionConMensaje(
        int $detalleOrdenId,
        int $cantidadDevuelta,
        ?string $observaciones
    ): array {
        try {
            $this->transactionService->beginTransaction();

            $devolucion = Devolucion::registrarDevolucion(
                $detalleOrdenId,
                $cantidadDevuelta,
                $observaciones
            );

            $this->transactionService->commit();

            $mensaje = $this->construirMensajeDevolucion($devolucion);

            return [
                'devolucion' => $devolucion,
                'mensaje' => $mensaje
            ];

        } catch (\Exception $e) {
            $this->transactionService->rollBack();
            throw new DevolucionException('Error al registrar la devolución: ' . $e->getMessage());
        }
    }

    /**
     * Construye mensaje informativo sobre la devolución
     *
     * @param Devolucion $devolucion
     * @return string
     */
    protected function construirMensajeDevolucion(Devolucion $devolucion): string
    {
        $mensaje = 'Devolución registrada exitosamente.';

        if ($devolucion->cierra_sin_stock) {
            $mensaje .= ' Se registró el consumo total sin restaurar stock.';
        }

        $diasRetraso = $devolucion->getDiasRetrasoDevolucion();
        if ($diasRetraso > 0) {
            $mensaje .= " NOTA: La devolución se realizó con {$diasRetraso} días de retraso.";
        }

        return $mensaje;
    }

    /**
     * Obtiene el estado APROBADA
     * Uso directo del modelo Tema/Parametro (clase externa, sin SOLID)
     *
     * @return \App\Models\Parametro
     * @throws DevolucionException
     */
    public function obtenerEstadoAprobada()
    {
        $tema = Tema::where('name', 'ESTADOS DE ORDEN')->first();
        if (!$tema) {
            throw new DevolucionException("Tema 'ESTADOS DE ORDEN' no encontrado.");
        }

        $estado = $tema->parametros()
            ->where('name', 'APROBADA')
            ->wherePivot('status', 1)
            ->first();

        if (!$estado) {
            throw new DevolucionException("Estado 'APROBADA' no encontrado.");
        }

        return $estado;
    }
}

