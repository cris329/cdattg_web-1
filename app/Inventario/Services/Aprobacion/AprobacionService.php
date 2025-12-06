<?php

declare(strict_types=1);

namespace App\Inventario\Services\Aprobacion;

use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Orden;
use App\Exceptions\AprobacionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrdenAprobadaNotification;
use App\Notifications\OrdenRechazadaNotification;
use App\Inventario\Interfaces\Repositories\Aprobacion\AprobacionRepositoryInterface;
use App\Inventario\Interfaces\Repositories\Orden\DetalleOrdenRepositoryInterface;
use App\Inventario\Interfaces\Repositories\Orden\OrdenRepositoryInterface;
use App\Inventario\Interfaces\Repositories\Producto\ProductoRepositoryInterface;
use App\Inventario\Interfaces\Services\TransactionServiceInterface;
use App\Inventario\Interfaces\Services\StockValidatorServiceInterface;
use App\Inventario\Interfaces\Services\FormOptionsServiceInterface;
use Throwable;

class AprobacionService
{
    private const STATUS_PENDING = 'EN ESPERA';
    private const STATUS_APPROVED = 'APROBADA';
    private const STATUS_REJECTED = 'RECHAZADA';
    private const ORDER_STATUS_THEME = 'ESTADOS DE ORDEN';
    private const ERROR_ESTADO_EN_ESPERA_NO_ENCONTRADO = "Estado 'EN ESPERA' no encontrado.";

    protected AprobacionRepositoryInterface $repository;
    protected DetalleOrdenRepositoryInterface $detalleOrdenRepository;
    protected OrdenRepositoryInterface $ordenRepository;
    protected ProductoRepositoryInterface $productoRepository;
    protected TransactionServiceInterface $transactionService;
    protected StockValidatorServiceInterface $stockValidator;
    protected FormOptionsServiceInterface $formOptionsService;

    public function __construct(
        AprobacionRepositoryInterface $repository,
        DetalleOrdenRepositoryInterface $detalleOrdenRepository,
        OrdenRepositoryInterface $ordenRepository,
        ProductoRepositoryInterface $productoRepository,
        TransactionServiceInterface $transactionService,
        StockValidatorServiceInterface $stockValidator,
        FormOptionsServiceInterface $formOptionsService
    ) {
        $this->repository = $repository;
        $this->detalleOrdenRepository = $detalleOrdenRepository;
        $this->ordenRepository = $ordenRepository;
        $this->productoRepository = $productoRepository;
        $this->transactionService = $transactionService;
        $this->stockValidator = $stockValidator;
        $this->formOptionsService = $formOptionsService;
    }

    /**
     * @return \App\Models\ParametroTema|null
     */
    public function obtenerEstadoEnEspera(): ?\App\Models\ParametroTema
    {
        return $this->obtenerEstadoPorNombre(self::STATUS_PENDING);
    }

    /**
     * @return \App\Models\ParametroTema
     * @throws AprobacionException
     */
    public function obtenerEstadoAprobada(): \App\Models\ParametroTema
    {
        $parametroTema = $this->obtenerEstadoPorNombre(self::STATUS_APPROVED);
        if (!$parametroTema) {
            throw new AprobacionException("Estado '" . self::STATUS_APPROVED . "' no encontrado en '" . self::ORDER_STATUS_THEME . "'.");
        }
        return $parametroTema;
    }

    /**
     * @return \App\Models\ParametroTema
     * @throws AprobacionException
     */
    public function obtenerEstadoRechazada(): \App\Models\ParametroTema
    {
        $parametroTema = $this->obtenerEstadoPorNombre(self::STATUS_REJECTED);
        if (!$parametroTema) {
            throw new AprobacionException("Estado '" . self::STATUS_REJECTED . "' no encontrado en '" . self::ORDER_STATUS_THEME . "'.");
        }
        return $parametroTema;
    }

    /**
     * Obtiene un estado por nombre en el tema de estados de orden.
     * Usa FormOptionsService para centralizar acceso a Tema (SRP)
     *
     * @param string $name
     * @return \App\Models\ParametroTema|null
     */
    private function obtenerEstadoPorNombre(string $name): ?\App\Models\ParametroTema
    {
        return $this->formOptionsService->obtenerEstadoOrdenPorNombre($name, self::ORDER_STATUS_THEME);
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
            if (!$estadoEnEspera) {
                throw new AprobacionException(self::ERROR_ESTADO_EN_ESPERA_NO_ENCONTRADO);
            }

            $estadoAprobada = $this->obtenerEstadoAprobada();

            $this->validarDetallePendiente($detalleOrden, $estadoEnEspera);

            $producto = $detalleOrden->producto;

            // Validar stock y considerar lock para evitar race condition
            $this->stockValidator->validarStockSuficiente($producto, $detalleOrden->cantidad);

            $this->detalleOrdenRepository->actualizar($detalleOrden, [
                'estado_orden_id' => $estadoAprobada->id,
                'user_update_id' => Auth::id(),
            ]);

            $this->repository->crear([
                'detalle_orden_id' => $detalleOrden->id,
                'estado_aprobacion_id' => $estadoAprobada->id,
                'user_create_id' => Auth::id(),
                'user_update_id' => Auth::id(),
            ]);

            $nuevaCantidad = $producto->cantidad - $detalleOrden->cantidad;
            $this->productoRepository->actualizarStock($producto, $nuevaCantidad);
            $this->productoRepository->actualizar($producto, ['user_update_id' => Auth::id()]);

            $this->notificarAprobacion($detalleOrden);

            $this->transactionService->commit();
        } catch (Throwable $e) {
            $this->transactionService->rollBack();
            throw $e instanceof AprobacionException ? $e : new AprobacionException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Valida que el detalle esté pendiente de aprobación
     *
     * @param DetalleOrden $detalleOrden
     * @param \App\Models\ParametroTema|null $estadoEnEspera
     * @return void
     * @throws AprobacionException
     */
    private function validarDetallePendiente(DetalleOrden $detalleOrden, ?\App\Models\ParametroTema $estadoEnEspera): void
    {
        if (!$estadoEnEspera || $detalleOrden->estado_orden_id != $estadoEnEspera->id) {
            throw new AprobacionException('Esta solicitud no está pendiente de aprobación.');
        }

        if ($detalleOrden->aprobacion) {
            throw new AprobacionException('Esta solicitud ya fue procesada anteriormente.');
        }
    }

    /**
     * Notifica la aprobación al solicitante (en cola si el driver de notificaciones está configurado)
     *
     * @param DetalleOrden $detalleOrden
     * @return void
     */
    private function notificarAprobacion(DetalleOrden $detalleOrden): void
    {
        $solicitante = $detalleOrden->orden->userCreate;
        if (! $solicitante) {
            return;
        }

        // Use Notification facade for potential queueing
        Notification::send($solicitante, new OrdenAprobadaNotification($detalleOrden, Auth::user()));
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
        if (! $solicitante) {
            return;
        }

        Notification::send($solicitante, new OrdenRechazadaNotification($detalleOrden, Auth::user(), $motivoRechazo));
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
            if (!$estadoEnEspera) {
                throw new AprobacionException(self::ERROR_ESTADO_EN_ESPERA_NO_ENCONTRADO);
            }

            $estadoRechazada = $this->obtenerEstadoRechazada();

            $this->validarDetallePendiente($detalleOrden, $estadoEnEspera);

            $this->detalleOrdenRepository->actualizar($detalleOrden, [
                'estado_orden_id' => $estadoRechazada->id,
                'user_update_id' => Auth::id(),
            ]);

            $this->repository->crear([
                'detalle_orden_id' => $detalleOrden->id,
                'estado_aprobacion_id' => $estadoRechazada->id,
                'user_create_id' => Auth::id(),
                'user_update_id' => Auth::id(),
            ]);

            $orden = $detalleOrden->orden;
            $descripcionActualizada = $this->construirDescripcionRechazo($orden->descripcion_orden, $detalleOrden, $motivoRechazo);

            $this->ordenRepository->actualizar($orden, [
                'descripcion_orden' => $descripcionActualizada,
                'user_update_id' => Auth::id(),
            ]);

            $this->notificarRechazo($detalleOrden, $motivoRechazo);

            $this->transactionService->commit();
        } catch (Throwable $e) {
            $this->transactionService->rollBack();
            throw $e instanceof AprobacionException ? $e : new AprobacionException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Construye descripción al rechazar (extraído para testear / reutilizar)
     */
    private function construirDescripcionRechazo(string $descripcionAnterior, DetalleOrden $detalleOrden, string $motivoRechazo): string
    {
        $texto = $descripcionAnterior . "\n\n--- SOLICITUD RECHAZADA ---\n";
        $texto .= "Producto: {$detalleOrden->producto->producto}\n";
        $texto .= "Motivo: {$motivoRechazo}\n";
        $texto .= "Rechazado por: " . Auth::user()->name . "\n";
        $texto .= "Fecha: " . now()->format('d/m/Y H:i') . "\n";
        return $texto;
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
            if (!$estadoEnEspera) {
                throw new AprobacionException(self::ERROR_ESTADO_EN_ESPERA_NO_ENCONTRADO);
            }

            $estadoAprobada = $this->obtenerEstadoAprobada();

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
                    'user_update_id' => Auth::id(),
                ]);

                $this->repository->crear([
                    'detalle_orden_id' => $detalle->id,
                    'estado_aprobacion_id' => $estadoAprobada->id,
                    'user_create_id' => Auth::id(),
                    'user_update_id' => Auth::id(),
                ]);

                $nuevaCantidad = $detalle->producto->cantidad - $detalle->cantidad;
                $this->productoRepository->actualizarStock($detalle->producto, $nuevaCantidad);
                $this->productoRepository->actualizar($detalle->producto, ['user_update_id' => Auth::id()]);
            }

            // Notificar al solicitante (en lote)
            $solicitante = $orden->userCreate;
            if ($solicitante) {
                foreach ($detallesPendientes as $detalle) {
                    Notification::send($solicitante, new OrdenAprobadaNotification($detalle, Auth::user()));
                }
            }

            $this->transactionService->commit();
        } catch (Throwable $e) {
            $this->transactionService->rollBack();
            throw $e instanceof AprobacionException ? $e : new AprobacionException($e->getMessage(), 0, $e);
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
            if (!$estadoEnEspera) {
                throw new AprobacionException(self::ERROR_ESTADO_EN_ESPERA_NO_ENCONTRADO);
            }

            $estadoRechazada = $this->obtenerEstadoRechazada();

            $detallesPendientes = $orden->detalles->where('estado_orden_id', $estadoEnEspera->id);

            if ($detallesPendientes->isEmpty()) {
                throw new AprobacionException('No hay productos pendientes de aprobación en esta orden.');
            }

            foreach ($detallesPendientes as $detalle) {
                $this->detalleOrdenRepository->actualizar($detalle, [
                    'estado_orden_id' => $estadoRechazada->id,
                    'user_update_id' => Auth::id(),
                ]);

                $this->repository->crear([
                    'detalle_orden_id' => $detalle->id,
                    'estado_aprobacion_id' => $estadoRechazada->id,
                    'user_create_id' => Auth::id(),
                    'user_update_id' => Auth::id(),
                ]);
            }

            $descripcionActualizada = $this->construirDescripcionRechazo($orden->descripcion_orden, $detallesPendientes->first(), $motivoRechazo);
            $descripcionActualizada = str_replace('--- SOLICITUD RECHAZADA ---', '--- ORDEN RECHAZADA COMPLETA ---', $descripcionActualizada);

            $this->ordenRepository->actualizar($orden, [
                'descripcion_orden' => $descripcionActualizada,
                'user_update_id' => Auth::id(),
            ]);

            $solicitante = $orden->userCreate;
            if ($solicitante) {
                foreach ($detallesPendientes as $detalle) {
                    Notification::send($solicitante, new OrdenRechazadaNotification($detalle, Auth::user(), $motivoRechazo));
                }
            }

            $this->transactionService->commit();
        } catch (Throwable $e) {
            $this->transactionService->rollBack();
            throw $e instanceof AprobacionException ? $e : new AprobacionException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Obtiene detalles pendientes de aprobación
     *
     * @return \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection
     */
    public function obtenerDetallesPendientes()
    {
        $estadoEnEspera = $this->obtenerEstadoEnEspera();

        if (!$estadoEnEspera || !isset($estadoEnEspera->id)) {
            return \Illuminate\Support\Collection::make([]);
        }

        $detalles = $this->ordenRepository->obtenerDetallesPendientes($estadoEnEspera->id);
        
        // Asegurar que siempre sea una colección (convertir Eloquent Collection a Support Collection si es necesario)
        return $detalles instanceof \Illuminate\Database\Eloquent\Collection
            ? $detalles
            : \Illuminate\Support\Collection::make($detalles);
    }

    /**
     * Encuentra un detalle de orden con sus relaciones
     *
     * @param int $detalleOrdenId
     * @return DetalleOrden|null
     */
    public function encontrarDetalleConRelaciones(int $detalleOrdenId): ?DetalleOrden
    {
        return $this->detalleOrdenRepository->encontrarConRelaciones($detalleOrdenId);
    }

    /**
     * Encuentra una orden con detalles y devoluciones
     *
     * @param int $ordenId
     * @return Orden|null
     */
    public function encontrarOrdenConDetallesYDevoluciones(int $ordenId): ?Orden
    {
        return $this->ordenRepository->encontrarConDetallesYDevoluciones($ordenId);
    }
}

