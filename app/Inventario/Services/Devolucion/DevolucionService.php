<?php

declare(strict_types=1);

namespace App\Inventario\Services\Devolucion;

use App\Exceptions\DevolucionException;
use App\Models\Inventario\Devolucion;
use App\Models\Tema;
use App\Inventario\Interfaces\Services\NotificationServiceInterface;
use App\Inventario\Interfaces\Services\TransactionServiceInterface;

/**
 * Servicio para gestión de devoluciones
 * Cumple SRP: responsabilidad única de gestionar devoluciones
 */
class DevolucionService
{
    protected TransactionServiceInterface $transactionService;
    protected NotificationServiceInterface $notificationService;

    public function __construct(
        TransactionServiceInterface $transactionService,
        NotificationServiceInterface $notificationService
    ) {
        $this->transactionService = $transactionService;
        $this->notificationService = $notificationService;
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
        $devolucion = null;

        try {
            $this->transactionService->beginTransaction();

            $devolucion = Devolucion::registrarDevolucion(
                $detalleOrdenId,
                $cantidadDevuelta,
                $observaciones
            );

            $this->transactionService->commit();
        } catch (\Exception $e) {
            $this->transactionService->rollBack();
            throw new DevolucionException('Error al registrar la devolución: ' . $e->getMessage());
        }

        try {
            $this->notificationService->notificarDevolucion($devolucion);
        } catch (\Exception $e) {
            throw new DevolucionException('Error al notificar la devolución: ' . $e->getMessage());
        }

        $mensaje = $this->construirMensajeDevolucion($devolucion);

        return [
            'devolucion' => $devolucion,
            'mensaje' => $mensaje
        ];
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
     * Obtiene el estado APROBADA como ParametroTema
     * Necesita devolver ParametroTema porque el repositorio usa estado_orden_id que referencia a parametros_temas
     *
     * @return \App\Models\ParametroTema
     * @throws DevolucionException
     */
    public function obtenerEstadoAprobada()
    {
        $tema = Tema::where('name', 'ESTADOS DE ORDEN')->first();
        if (!$tema) {
            throw new DevolucionException("Tema 'ESTADOS DE ORDEN' no encontrado.");
        }

        $parametro = \App\Models\Parametro::where('name', 'APROBADA')->first();
        if (!$parametro) {
            throw new DevolucionException("Parámetro 'APROBADA' no encontrado.");
        }

        $parametroTema = \App\Models\ParametroTema::where('tema_id', $tema->id)
            ->where('parametro_id', $parametro->id)
            ->where('status', 1)
            ->first();

        if (!$parametroTema) {
            throw new DevolucionException("Estado 'APROBADA' no encontrado en el tema 'ESTADOS DE ORDEN'.");
        }

        return $parametroTema;
    }
}

