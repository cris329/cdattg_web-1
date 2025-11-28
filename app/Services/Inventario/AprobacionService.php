<?php

declare(strict_types=1);

namespace App\Services\Inventario;

use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Orden;
use App\Repositories\Interfaces\Inventario\AprobacionRepositoryInterface;
use App\Repositories\Interfaces\Inventario\DetalleOrdenRepositoryInterface;
use App\Repositories\Interfaces\Inventario\OrdenRepositoryInterface;
use App\Repositories\Interfaces\Inventario\ProductoRepositoryInterface;
use App\Services\Inventario\Interfaces\TransactionServiceInterface;
use App\Services\Inventario\Interfaces\NotificationServiceInterface;
use App\Services\Inventario\Interfaces\StockValidatorServiceInterface;
use App\Models\Tema;
use App\Models\Parametro;
use App\Exceptions\AprobacionException;
use Illuminate\Support\Facades\Auth;
use App\Notifications\OrdenAprobadaNotification;
use App\Notifications\OrdenRechazadaNotification;

class AprobacionService
{
    protected AprobacionRepositoryInterface $repository;
    protected DetalleOrdenRepositoryInterface $detalleOrdenRepository;
    protected OrdenRepositoryInterface $ordenRepository;
    protected ProductoRepositoryInterface $productoRepository;
    protected TransactionServiceInterface $transactionService;
    protected StockValidatorServiceInterface $stockValidator;

    public function __construct(
        AprobacionRepositoryInterface $repository,
        DetalleOrdenRepositoryInterface $detalleOrdenRepository,
        OrdenRepositoryInterface $ordenRepository,
        ProductoRepositoryInterface $productoRepository,
        TransactionServiceInterface $transactionService,
        StockValidatorServiceInterface $stockValidator
    ) {
        $this->repository = $repository;
        $this->detalleOrdenRepository = $detalleOrdenRepository;
        $this->ordenRepository = $ordenRepository;
        $this->productoRepository = $productoRepository;
        $this->transactionService = $transactionService;
        $this->stockValidator = $stockValidator;
    }
    private const STATUS_PENDING = 'EN ESPERA';
    private const STATUS_APPROVED = 'APROBADA';
    private const STATUS_REJECTED = 'RECHAZADA';
    private const ORDER_STATUS_THEME = 'ESTADOS DE ORDEN';

    /**
     * Obtiene estado EN ESPERA
     * Uso directo del modelo Tema/Parametro (clase externa, sin SOLID)
     *
     * @return Parametro|null
     */
    public function obtenerEstadoEnEspera()
    {
        $tema = Tema::where('name', self::ORDER_STATUS_THEME)->first();
        if (!$tema) {
            return null;
        }

        return $tema->parametros()
            ->where('name', self::STATUS_PENDING)
            ->wherePivot('status', 1)
            ->first();
    }

    /**
     * Obtiene estado APROBADA
     * Uso directo del modelo Tema/Parametro (clase externa, sin SOLID)
     *
     * @return Parametro
     * @throws AprobacionException
     */
    public function obtenerEstadoAprobada()
    {
        $tema = Tema::where('name', self::ORDER_STATUS_THEME)->first();
        if (!$tema) {
            throw new AprobacionException("Tema 'ESTADOS DE ORDEN' no encontrado.");
        }

        $estado = $tema->parametros()
            ->where('name', self::STATUS_APPROVED)
            ->wherePivot('status', 1)
            ->first();

        if (!$estado) {
            throw new AprobacionException("Estado 'APROBADA' no encontrado en parámetros.");
        }

        return $estado;
    }

    /**
     * Obtiene estado RECHAZADA
     * Uso directo del modelo Tema/Parametro (clase externa, sin SOLID)
     *
     * @return Parametro
     * @throws AprobacionException
     */
    public function obtenerEstadoRechazada()
    {
        $tema = Tema::where('name', self::ORDER_STATUS_THEME)->first();
        if (!$tema) {
            throw new AprobacionException("Tema 'ESTADOS DE ORDEN' no encontrado.");
        }

        $estado = $tema->parametros()
            ->where('name', self::STATUS_REJECTED)
            ->wherePivot('status', 1)
            ->first();

        if (!$estado) {
            throw new AprobacionException("Estado 'RECHAZADA' no encontrado en parámetros.");
        }

        return $estado;
    }

    /**
     * Aprueba un detalle de orden
     *
     * @param DetalleOrden $detalleOrden
     * @return void
     * @throws AprobacionException
     */
    public function aprobarDetalle(DetalleOrden $detalleOrden): void
    {
        try {
            $this->transactionService->beginTransaction();

            $estadoEnEspera = $this->obtenerEstadoEnEspera();
            $estadoAprobada = $this->obtenerEstadoAprobada();

            $this->validarDetallePendiente($detalleOrden, $estadoEnEspera);

            $producto = $detalleOrden->producto;
            $this->stockValidator->validarStockSuficiente($producto, $detalleOrden->cantidad);

            $this->detalleOrdenRepository->actualizar($detalleOrden, [
                'estado_orden_id' => $estadoAprobada->id,
                'user_update_id' => Auth::id()
            ]);

            $this->repository->crear([
                'detalle_orden_id' => $detalleOrden->id,
                'estado_aprobacion_id' => $estadoAprobada->id,
                'user_create_id' => Auth::id(),
                'user_update_id' => Auth::id()
            ]);

            $nuevaCantidad = $producto->cantidad - $detalleOrden->cantidad;
            $this->productoRepository->actualizarStock($producto, $nuevaCantidad);
            $this->productoRepository->actualizar($producto, ['user_update_id' => Auth::id()]);

            $this->notificarAprobacion($detalleOrden);

            $this->transactionService->commit();

        } catch (\Exception $e) {
            $this->transactionService->rollBack();
            throw $e;
        }
    }

    /**
     * Valida que el detalle esté pendiente de aprobación
     *
     * @param DetalleOrden $detalleOrden
     * @param mixed $estadoEnEspera
     * @return void
     * @throws AprobacionException
     */
    private function validarDetallePendiente(DetalleOrden $detalleOrden, $estadoEnEspera): void
    {
        if (!$estadoEnEspera || $detalleOrden->estado_orden_id != $estadoEnEspera->id) {
            throw new AprobacionException('Esta solicitud no está pendiente de aprobación.');
        }

        if ($detalleOrden->aprobacion) {
            throw new AprobacionException('Esta solicitud ya fue procesada anteriormente.');
        }
    }

    /**
     * Notifica la aprobación al solicitante
     *
     * @param DetalleOrden $detalleOrden
     * @return void
     */
    private function notificarAprobacion(DetalleOrden $detalleOrden): void
    {
        $solicitante = $detalleOrden->orden->userCreate;
        if ($solicitante) {
            $solicitante->notify(new OrdenAprobadaNotification($detalleOrden, Auth::user()));
        }
    }

    /**
     * Notifica el rechazo al solicitante
     *
     * @param DetalleOrden $detalleOrden
     * @param string $motivoRechazo
     * @return void
     */
    private function notificarRechazo(DetalleOrden $detalleOrden, string $motivoRechazo): void
    {
        $solicitante = $detalleOrden->orden->userCreate;
        if ($solicitante) {
            $solicitante->notify(new OrdenRechazadaNotification(
                $detalleOrden,
                Auth::user(),
                $motivoRechazo
            ));
        }
    }

    /**
     * Rechaza un detalle de orden
     *
     * @param DetalleOrden $detalleOrden
     * @param string $motivoRechazo
     * @return void
     * @throws AprobacionException
     */
    public function rechazarDetalle(DetalleOrden $detalleOrden, string $motivoRechazo): void
    {
        try {
            $this->transactionService->beginTransaction();

            $estadoEnEspera = $this->obtenerEstadoEnEspera();
            $estadoRechazada = $this->obtenerEstadoRechazada();

            $this->validarDetallePendiente($detalleOrden, $estadoEnEspera);

            $this->detalleOrdenRepository->actualizar($detalleOrden, [
                'estado_orden_id' => $estadoRechazada->id,
                'user_update_id' => Auth::id()
            ]);

            $this->repository->crear([
                'detalle_orden_id' => $detalleOrden->id,
                'estado_aprobacion_id' => $estadoRechazada->id,
                'user_create_id' => Auth::id(),
                'user_update_id' => Auth::id()
            ]);

            $orden = $detalleOrden->orden;
            $descripcionActualizada = $orden->descripcion_orden . "\n\n--- SOLICITUD RECHAZADA ---\n";
            $descripcionActualizada .= "Producto: {$detalleOrden->producto->producto}\n";
            $descripcionActualizada .= "Motivo: {$motivoRechazo}\n";
            $descripcionActualizada .= "Rechazado por: " . Auth::user()->name . "\n";
            $descripcionActualizada .= "Fecha: " . now()->format('d/m/Y H:i') . "\n";
            
            $this->ordenRepository->actualizar($orden, [
                'descripcion_orden' => $descripcionActualizada,
                'user_update_id' => Auth::id()
            ]);

            $this->notificarRechazo($detalleOrden, $motivoRechazo);

            $this->transactionService->commit();

        } catch (\Exception $e) {
            $this->transactionService->rollBack();
            throw $e;
        }
    }

    /**
     * Aprueba toda una orden completa
     *
     * @param Orden $orden
     * @return void
     * @throws AprobacionException
     */
    public function aprobarOrdenCompleta(Orden $orden): void
    {
        try {
            $this->transactionService->beginTransaction();

            $estadoEnEspera = $this->obtenerEstadoEnEspera();
            $estadoAprobada = $this->obtenerEstadoAprobada();

            if (!$estadoEnEspera) {
                throw new AprobacionException("Estado 'EN ESPERA' no encontrado.");
            }

            $detallesPendientes = $orden->detalles->where('estado_orden_id', $estadoEnEspera->id);

            if ($detallesPendientes->isEmpty()) {
                throw new AprobacionException('No hay productos pendientes de aprobación en esta orden.');
            }

            // Validar stock de todos los productos antes de procesar
            foreach ($detallesPendientes as $detalle) {
                $this->stockValidator->validarStockSuficiente($detalle->producto, $detalle->cantidad);
            }

            foreach ($detallesPendientes as $detalle) {
                $this->detalleOrdenRepository->actualizar($detalle, [
                    'estado_orden_id' => $estadoAprobada->id,
                    'user_update_id' => Auth::id()
                ]);

                $this->repository->crear([
                    'detalle_orden_id' => $detalle->id,
                    'estado_aprobacion_id' => $estadoAprobada->id,
                    'user_create_id' => Auth::id(),
                    'user_update_id' => Auth::id()
                ]);

                $nuevaCantidad = $detalle->producto->cantidad - $detalle->cantidad;
                $this->productoRepository->actualizarStock($detalle->producto, $nuevaCantidad);
                $this->productoRepository->actualizar($detalle->producto, ['user_update_id' => Auth::id()]);
            }

            $solicitante = $orden->userCreate;
            if ($solicitante) {
                foreach ($detallesPendientes as $detalle) {
                    $solicitante->notify(new OrdenAprobadaNotification($detalle, Auth::user()));
                }
            }

            $this->transactionService->commit();

        } catch (\Exception $e) {
            $this->transactionService->rollBack();
            throw $e;
        }
    }

    /**
     * Rechaza toda una orden completa
     *
     * @param Orden $orden
     * @param string $motivoRechazo
     * @return void
     * @throws AprobacionException
     */
    public function rechazarOrdenCompleta(Orden $orden, string $motivoRechazo): void
    {
        try {
            $this->transactionService->beginTransaction();

            $estadoEnEspera = $this->obtenerEstadoEnEspera();
            $estadoRechazada = $this->obtenerEstadoRechazada();

            if (!$estadoEnEspera) {
                throw new AprobacionException("Estado 'EN ESPERA' no encontrado.");
            }

            $detallesPendientes = $orden->detalles->where('estado_orden_id', $estadoEnEspera->id);

            if ($detallesPendientes->isEmpty()) {
                throw new AprobacionException('No hay productos pendientes de aprobación en esta orden.');
            }

            foreach ($detallesPendientes as $detalle) {
                $this->detalleOrdenRepository->actualizar($detalle, [
                    'estado_orden_id' => $estadoRechazada->id,
                    'user_update_id' => Auth::id()
                ]);

                $this->repository->crear([
                    'detalle_orden_id' => $detalle->id,
                    'estado_aprobacion_id' => $estadoRechazada->id,
                    'user_create_id' => Auth::id(),
                    'user_update_id' => Auth::id()
                ]);
            }

            $descripcionActualizada = $orden->descripcion_orden . "\n\n--- ORDEN RECHAZADA COMPLETA ---\n";
            $descripcionActualizada .= "Motivo: {$motivoRechazo}\n";
            $descripcionActualizada .= "Rechazado por: " . Auth::user()->name . "\n";
            $descripcionActualizada .= "Fecha: " . now()->format('d/m/Y H:i') . "\n";
            
            $this->ordenRepository->actualizar($orden, [
                'descripcion_orden' => $descripcionActualizada,
                'user_update_id' => Auth::id()
            ]);

            $solicitante = $orden->userCreate;
            if ($solicitante) {
                foreach ($detallesPendientes as $detalle) {
                    $solicitante->notify(new OrdenRechazadaNotification(
                        $detalle,
                        Auth::user(),
                        $motivoRechazo
                    ));
                }
            }

            $this->transactionService->commit();

        } catch (\Exception $e) {
            $this->transactionService->rollBack();
            throw $e;
        }
    }
}

