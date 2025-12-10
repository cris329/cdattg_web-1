<?php

declare(strict_types=1);

namespace App\Inventario\Services\Orden;

use App\Models\Inventario\Orden;
use App\Models\Inventario\DetalleOrden;
use App\Inventario\Interfaces\Repositories\Orden\OrdenRepositoryInterface;
use App\Inventario\Interfaces\Repositories\Orden\DetalleOrdenRepositoryInterface;
use App\Inventario\Interfaces\Repositories\Producto\ProductoRepositoryInterface;
use App\Inventario\Interfaces\Repositories\ParametroTema\ParametroTemaRepositoryInterface;
use App\Inventario\Interfaces\Services\NotificationServiceInterface;
use App\Inventario\Interfaces\Services\TransactionServiceInterface;
use App\Inventario\Interfaces\Services\StockValidatorServiceInterface;
use App\Models\ParametroTema;
use App\Models\Tema;
use App\Models\Parametro;
use App\Exceptions\OrdenException;
use Illuminate\Support\Facades\Auth;

class OrdenService
{
    private const THEME_ORDER_STATES = 'ESTADOS DE ORDEN';
    private const STATUS_EN_ESPERA = 'EN ESPERA';
    private const STATUS_APROBADA = 'APROBADA';

    protected OrdenRepositoryInterface $ordenRepository;
    protected DetalleOrdenRepositoryInterface $detalleOrdenRepository;
    protected ProductoRepositoryInterface $productoRepository;
    protected ParametroTemaRepositoryInterface $parametroTemaRepository;
    protected NotificationServiceInterface $notificationService;
    protected TransactionServiceInterface $transactionService;
    protected StockValidatorServiceInterface $stockValidator;

    public function __construct(
        OrdenRepositoryInterface $ordenRepository,
        DetalleOrdenRepositoryInterface $detalleOrdenRepository,
        ProductoRepositoryInterface $productoRepository,
        ParametroTemaRepositoryInterface $parametroTemaRepository,
        NotificationServiceInterface $notificationService,
        TransactionServiceInterface $transactionService,
        StockValidatorServiceInterface $stockValidator
    ) {
        $this->ordenRepository = $ordenRepository;
        $this->detalleOrdenRepository = $detalleOrdenRepository;
        $this->productoRepository = $productoRepository;
        $this->parametroTemaRepository = $parametroTemaRepository;
        $this->notificationService = $notificationService;
        $this->transactionService = $transactionService;
        $this->stockValidator = $stockValidator;
    }

    /**
     * Crea una nueva orden con sus detalles
     *
     * @param array $datos
     * @param int $userId
     * @return Orden
     * @throws OrdenException
     */
    public function crear(array $datos, int $userId): Orden
    {
        try {
            $this->transactionService->beginTransaction();

            $orden = $this->ordenRepository->crear([
                'descripcion_orden' => $datos['descripcion_orden'],
                'tipo_orden_id' => $datos['tipo_orden_id'],
                'fecha_devolucion' => $datos['fecha_devolucion'] ?? null,
                'user_create_id' => $userId,
                'user_update_id' => $userId
            ]);

            foreach ($datos['productos'] as $productoData) {
                $this->procesarDetalleOrden($orden, $productoData, $userId);
            }

            $this->transactionService->commit();

            return $orden;

        } catch (\Exception $e) {
            $this->transactionService->rollBack();
            throw new OrdenException('Error al crear la orden: ' . $e->getMessage());
        }
    }

    /**
     * Crea una orden de préstamo/salida desde carrito
     *
     * @param array $datos
     * @param int $userId
     * @return Orden
     * @throws OrdenException
     */
    public function crearDesdeCarrito(array $datos, int $userId): Orden
    {
        try {
            $this->transactionService->beginTransaction();

            $carrito = json_decode($datos['carrito'], true);

            if (empty($carrito) || !is_array($carrito)) {
                throw new OrdenException('El carrito está vacío.');
            }

            $tipoMap = [
                'prestamo' => 'PRÉSTAMO',
                'salida' => 'SALIDA'
            ];

            $codigoTipoOrden = $tipoMap[$datos['tipo']] ?? strtoupper($datos['tipo']);
            $parametroTipoOrden = $this->obtenerParametroTipoOrden($codigoTipoOrden);
            $estadoEnEspera = $this->obtenerEstadoEnEspera();

            $usuario = Auth::user();
            $descripcionDetallada = $this->generarDescripcionOrden($datos, $usuario);

            $orden = $this->ordenRepository->crear([
                'descripcion_orden' => $descripcionDetallada,
                'tipo_orden_id' => $parametroTipoOrden->id,
                'fecha_devolucion' => $datos['tipo'] === 'prestamo' ? $datos['fecha_devolucion'] : null,
                'user_create_id' => $userId,
                'user_update_id' => $userId
            ]);

            foreach ($carrito as $item) {
                $productoId = $item['id'] ?? $item['producto_id'] ?? null;
                $cantidad = (int) ($item['quantity'] ?? $item['cantidad'] ?? 1);

                if (!$productoId) {
                    continue;
                }

                $producto = $this->productoRepository->encontrar((int) $productoId);

                if (!$producto) {
                    throw new OrdenException("Producto con ID {$productoId} no encontrado.");
                }

                $this->stockValidator->validarStockSuficiente($producto, $cantidad);

                $this->detalleOrdenRepository->crear([
                    'orden_id' => $orden->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'estado_orden_id' => $estadoEnEspera->id,
                    'user_create_id' => $userId,
                    'user_update_id' => $userId
                ]);
            }

            $this->notificarNuevaOrden($orden);

            $this->transactionService->commit();

            // Limpiar carrito después de crear la orden
            session()->forget('carrito_data');

            return $orden;

        } catch (OrdenException $e) {
            $this->transactionService->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $this->transactionService->rollBack();
            throw new OrdenException('Error al crear la orden: ' . $e->getMessage());
        }
    }

    /**
     * Procesa un detalle de orden
     *
     * @param Orden $orden
     * @param array $productoData
     * @param int $userId
     * @return void
     * @throws OrdenException
     */
    private function procesarDetalleOrden(Orden $orden, array $productoData, int $userId): void
    {
        $producto = $this->productoRepository->encontrar($productoData['producto_id']);

        if (!$producto) {
            throw new OrdenException("Producto con ID {$productoData['producto_id']} no encontrado.");
        }

        // Validar stock antes de procesar
        $this->stockValidator->validarStockSuficiente($producto, $productoData['cantidad']);

        $this->detalleOrdenRepository->crear([
            'orden_id' => $orden->id,
            'producto_id' => $producto->id,
            'cantidad' => $productoData['cantidad'],
            'estado_orden_id' => $productoData['estado_orden_id'],
            'user_create_id' => $userId,
            'user_update_id' => $userId
        ]);

        $nuevaCantidad = $producto->cantidad - $productoData['cantidad'];
        $this->productoRepository->actualizarStock($producto, $nuevaCantidad);
    }

    /**
     * Obtiene el tipo de orden como ParametroTema válido
     *
     * @param string $codigo
     * @return ParametroTema
     * @throws OrdenException
     */
    public function obtenerParametroTipoOrden(string $codigo): ParametroTema
    {
        $tema = Tema::where('name', 'TIPOS DE ORDEN')->first();
        if (!$tema) {
            throw new OrdenException("Tema 'TIPOS DE ORDEN' no encontrado.");
        }

        // Normalizar caracteres (quitar acentos, convertir a mayúsculas)
        $codigoNormalizado = strtoupper($codigo);
        $codigoNormalizado = str_replace(['É', 'Í', 'Ó'], ['E', 'I', 'O'], $codigoNormalizado);

        $parametro = $tema->parametros()
            ->where('name', $codigoNormalizado)
            ->wherePivot('status', 1)
            ->first();

        if (!$parametro) {
            throw new OrdenException("Tipo de orden '{$codigo}' no encontrado. Verifique los parámetros del sistema.");
        }

        $parametroTema = $this->parametroTemaRepository->obtenerPorTemaYParametro($tema->id, $parametro->id);

        if (!$parametroTema) {
            throw new OrdenException("Tipo de orden '{$codigo}' no está asociado correctamente al tema 'TIPOS DE ORDEN'.");
        }

        return $parametroTema;
    }

    /**
     * Obtiene estado EN ESPERA
     * Retorna ParametroTema porque estado_orden_id en DetalleOrden referencia a parametros_temas
     *
     * @return \App\Models\ParametroTema
     * @throws OrdenException
     */
    public function obtenerEstadoEnEspera()
    {
        $parametroTema = $this->parametroTemaRepository->obtenerEstadoPorNombre(self::STATUS_EN_ESPERA, self::THEME_ORDER_STATES);

        if (!$parametroTema) {
            throw new OrdenException("Estado 'EN ESPERA' no encontrado en 'ESTADOS DE ORDEN'. Verifique los parámetros del sistema.");
        }

        return $parametroTema;
    }

    /**
     * Obtiene estado APROBADA
     * Uso directo del modelo Tema/Parametro (clase externa, sin SOLID)
     *
     * @return Parametro
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

    /**
     * Genera descripción detallada de la orden
     *
     * @param array $datos
     * @param mixed $usuario
     * @return string
     */
    private function generarDescripcionOrden(array $datos, $usuario): string
    {
        $solicitante = $usuario->name ?? 'Usuario';
        $email = $usuario->email ?? '';

        return sprintf(
            "SOLICITUD DE %s\n\n" .
            "SOLICITANTE:\n" .
            "Nombre: %s\n" .
            "Email: %s\n" .
            "Rol: %s\n" .
            "Programa de Formación: %s\n\n" .
            "DETALLES:\n" .
            "Tipo: %s\n" .
            "%s\n" .
            "MOTIVO:\n%s",
            strtoupper($datos['tipo']),
            $solicitante,
            $email,
            $datos['rol'],
            $datos['programa_formacion'],
            ucfirst($datos['tipo']),
            $datos['tipo'] === 'prestamo' && !empty($datos['fecha_devolucion'])
                ? "Fecha de Devolución: {$datos['fecha_devolucion']}\n"
                : "Sin fecha de devolución\n",
            $datos['descripcion']
        );
    }

    /**
     * Notifica a administradores sobre nueva orden
     *
     * @param Orden $orden
     * @return void
     */
    private function notificarNuevaOrden(Orden $orden): void
    {
        $this->notificationService->notificarNuevaOrden($orden);
    }

    /**
     * Actualiza una orden existente
     *
     * @param Orden $orden
     * @param array $datos
     * @param int $userId
     * @return Orden
     * @throws OrdenException
     */
    public function actualizar(Orden $orden, array $datos, int $userId): Orden
    {
        try {
            $this->transactionService->beginTransaction();

            // Devolver stock de productos anteriores
            foreach ($orden->detalles as $detalle) {
                $producto = $detalle->producto;
                $nuevaCantidad = $producto->cantidad + $detalle->cantidad;
                $this->productoRepository->actualizarStock($producto, $nuevaCantidad);
            }

            // Eliminar detalles anteriores
            $this->detalleOrdenRepository->eliminarPorOrden($orden->id);

            // Actualizar la orden
            $this->ordenRepository->actualizar($orden, [
                'descripcion_orden' => $datos['descripcion_orden'],
                'tipo_orden_id' => $datos['tipo_orden_id'],
                'fecha_devolucion' => $datos['fecha_devolucion'] ?? null,
                'user_update_id' => $userId
            ]);
            $orden->refresh();

            // Procesar nuevos productos
            foreach ($datos['productos'] as $productoData) {
                $this->procesarDetalleOrden($orden, $productoData, $userId);
            }

            $this->transactionService->commit();

            return $orden;

        } catch (\Exception $e) {
            $this->transactionService->rollBack();
            throw new OrdenException('Error al actualizar la orden: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una orden y devuelve el stock
     *
     * @param Orden $orden
     * @return bool
     * @throws OrdenException
     */
    public function eliminar(Orden $orden): bool
    {
        try {
            $this->transactionService->beginTransaction();

            // Devolver stock de todos los productos
            foreach ($orden->detalles as $detalle) {
                $producto = $detalle->producto;
                $nuevaCantidad = $producto->cantidad + $detalle->cantidad;
                $this->productoRepository->actualizarStock($producto, $nuevaCantidad);
            }

            // Eliminar detalles
            $this->detalleOrdenRepository->eliminarPorOrden($orden->id);

            // Eliminar orden
            $resultado = $this->ordenRepository->eliminar($orden);

            $this->transactionService->commit();

            return $resultado;

        } catch (\Exception $e) {
            $this->transactionService->rollBack();
            throw new OrdenException('Error al eliminar la orden: ' . $e->getMessage());
        }
    }

    /**
     * Verifica si una orden tiene devoluciones registradas
     *
     * @param Orden $orden
     * @return bool
     */
    public function tieneDevoluciones(Orden $orden): bool
    {
        return $orden->detalles()->whereHas('devoluciones')->exists();
    }
}

