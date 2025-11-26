<?php

declare(strict_types=1);

namespace App\Services\Inventario;

use App\Repositories\Inventario\OrdenRepository;
use App\Models\Inventario\Orden;
use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Producto;
use App\Models\ParametroTema;
use App\Exceptions\OrdenException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NuevaOrdenNotification;

class OrdenService
{
    private const THEME_ORDER_STATES = 'ESTADOS DE ORDEN';
    private const THEME_ORDER_TYPES = 'TIPOS DE ORDEN';
    private const STATUS_EN_ESPERA = 'EN ESPERA';
    private const STATUS_APROBADA = 'APROBADA';

    protected OrdenRepository $repository;

    public function __construct(OrdenRepository $repository)
    {
        $this->repository = $repository;
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
            DB::beginTransaction();

            $orden = new Orden([
                'descripcion_orden' => $datos['descripcion_orden'],
                'tipo_orden_id' => $datos['tipo_orden_id'],
                'fecha_devolucion' => $datos['fecha_devolucion'] ?? null
            ]);
            $orden->user_create_id = $userId;
            $orden->user_update_id = $userId;
            $orden->save();

            foreach ($datos['productos'] as $productoData) {
                $this->procesarDetalleOrden($orden, $productoData, $userId);
            }

            DB::commit();

            return $orden;

        } catch (\Exception $e) {
            DB::rollBack();
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
            DB::beginTransaction();

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

            $orden = new Orden([
                'descripcion_orden' => $descripcionDetallada,
                'tipo_orden_id' => $parametroTipoOrden->id,
                'fecha_devolucion' => $datos['tipo'] === 'prestamo' ? $datos['fecha_devolucion'] : null
            ]);
            $orden->user_create_id = $userId;
            $orden->user_update_id = $userId;
            $orden->save();

            foreach ($carrito as $item) {
                $productoId = $item['id'] ?? $item['producto_id'] ?? null;
                $cantidad = (int) ($item['quantity'] ?? $item['cantidad'] ?? 1);

                if (!$productoId) {
                    continue;
                }

                $producto = Producto::find($productoId);
                
                if (!$producto) {
                    throw new OrdenException("Producto con ID {$productoId} no encontrado.");
                }

                if ($producto->cantidad < $cantidad) {
                    throw new OrdenException(
                        "Stock insuficiente para '{$producto->producto}'. " .
                        "Disponible: {$producto->cantidad}, Solicitado: {$cantidad}"
                    );
                }

                $detalle = new DetalleOrden([
                    'orden_id' => $orden->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'estado_orden_id' => $estadoEnEspera->id
                ]);
                $detalle->user_create_id = $userId;
                $detalle->user_update_id = $userId;
                $detalle->save();
            }

            $this->notificarNuevaOrden($orden);

            DB::commit();

            // Limpiar carrito después de crear la orden
            session()->forget('carrito_data');

            return $orden;

        } catch (OrdenException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
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
        $producto = Producto::findOrFail($productoData['producto_id']);

        if (!$producto->tieneStockDisponible($productoData['cantidad'])) {
            throw new OrdenException(
                "Stock insuficiente para el producto '{$producto->producto}'. " .
                "Disponible: {$producto->cantidad}, Solicitado: {$productoData['cantidad']}"
            );
        }

        $detalle = new DetalleOrden([
            'orden_id' => $orden->id,
            'producto_id' => $producto->id,
            'cantidad' => $productoData['cantidad'],
            'estado_orden_id' => $productoData['estado_orden_id']
        ]);
        $detalle->user_create_id = $userId;
        $detalle->user_update_id = $userId;
        $detalle->save();

        $producto->descontarStock($productoData['cantidad']);
    }

    /**
     * Obtiene parámetro de tipo de orden (con normalización de caracteres)
     *
     * @param string $codigo
     * @return ParametroTema
     * @throws OrdenException
     */
    public function obtenerParametroTipoOrden(string $codigo): ParametroTema
    {
        $parametro = ParametroTema::whereHas('tema', function ($q) {
            $q->whereRaw('UPPER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(name, "Á", "A"), "É", "E"), "Í", "I"), "Ó", "O"), "Ú", "U")) = ?', ['TIPOS DE ORDEN']);
        })
        ->whereHas('parametro', function ($q) use ($codigo) {
            $nombreNormalizado = str_replace(
                ['Á', 'É', 'Í', 'Ó', 'Ú'],
                ['A', 'E', 'I', 'O', 'U'],
                strtoupper($codigo)
            );
            $q->whereRaw('UPPER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(name, "Á", "A"), "É", "E"), "Í", "I"), "Ó", "O"), "Ú", "U")) = ?', [$nombreNormalizado]);
        })
        ->first();

        if (!$parametro) {
            throw new OrdenException("Tipo de orden '{$codigo}' no encontrado. Verifique los parámetros del sistema.");
        }

        return $parametro;
    }

    /**
     * Obtiene estado EN ESPERA
     *
     * @return ParametroTema
     * @throws OrdenException
     */
    public function obtenerEstadoEnEspera(): ParametroTema
    {
        $estado = ParametroTema::whereHas('tema', function ($q) {
            $q->whereRaw('UPPER(name) = ?', [self::THEME_ORDER_STATES]);
        })
        ->whereHas('parametro', function ($q) {
            $q->whereRaw('UPPER(name) = ?', ['EN ESPERA']);
        })
        ->first();

        if (!$estado) {
            throw new OrdenException("Estado 'EN ESPERA' no encontrado. Verifique los parámetros del sistema.");
        }

        return $estado;
    }

    /**
     * Obtiene estado APROBADA
     *
     * @return ParametroTema
     * @throws OrdenException
     */
    public function obtenerEstadoAprobada(): ParametroTema
    {
        $estado = ParametroTema::whereHas('tema', function ($q) {
            $q->where('name', self::THEME_ORDER_STATES);
        })
        ->whereHas('parametro', function ($q) {
            $q->where('name', self::STATUS_APROBADA);
        })
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
        $superadmins = \App\Models\User::role('SUPER ADMINISTRADOR')->get();
        
        if ($superadmins->isNotEmpty()) {
            Notification::send($superadmins, new NuevaOrdenNotification($orden));
        }
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
            DB::beginTransaction();

            // Devolver stock de productos anteriores
            foreach ($orden->detalles as $detalle) {
                $detalle->producto->devolverStock($detalle->cantidad);
            }

            // Eliminar detalles anteriores
            $orden->detalles()->delete();

            // Actualizar la orden
            $orden->fill([
                'descripcion_orden' => $datos['descripcion_orden'],
                'tipo_orden_id' => $datos['tipo_orden_id'],
                'fecha_devolucion' => $datos['fecha_devolucion'] ?? null
            ]);
            $orden->user_update_id = $userId;
            $orden->save();

            // Procesar nuevos productos
            foreach ($datos['productos'] as $productoData) {
                $this->procesarDetalleOrden($orden, $productoData, $userId);
            }

            DB::commit();

            return $orden;

        } catch (\Exception $e) {
            DB::rollBack();
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
            DB::beginTransaction();

            // Devolver stock de todos los productos
            foreach ($orden->detalles as $detalle) {
                $detalle->producto->devolverStock($detalle->cantidad);
            }

            // Eliminar detalles
            $orden->detalles()->delete();

            // Eliminar orden
            $resultado = $orden->delete();

            DB::commit();

            return $resultado;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new OrdenException('Error al eliminar la orden: ' . $e->getMessage());
        }
    }
}

