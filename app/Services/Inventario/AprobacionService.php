<?php

declare(strict_types=1);

namespace App\Services\Inventario;

use App\Models\Inventario\Aprobacion;
use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Orden;
use App\Models\ParametroTema;
use App\Exceptions\AprobacionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Notifications\OrdenAprobadaNotification;
use App\Notifications\OrdenRechazadaNotification;

class AprobacionService
{
    private const STATUS_PENDING = 'EN ESPERA';
    private const STATUS_APPROVED = 'APROBADA';
    private const STATUS_REJECTED = 'RECHAZADA';
    private const ORDER_STATUS_THEME = 'ESTADOS DE ORDEN';

    /**
     * Obtiene estado EN ESPERA
     *
     * @return ParametroTema|null
     */
    public function obtenerEstadoEnEspera(): ?ParametroTema
    {
        return ParametroTema::whereHas('parametro', function ($q) {
            $q->where('name', self::STATUS_PENDING);
        })
        ->whereHas('tema', function ($q) {
            $q->where('name', self::ORDER_STATUS_THEME);
        })
        ->first();
    }

    /**
     * Obtiene estado APROBADA
     *
     * @return ParametroTema
     * @throws AprobacionException
     */
    public function obtenerEstadoAprobada(): ParametroTema
    {
        $estado = ParametroTema::whereHas('parametro', function ($q) {
            $q->where('name', self::STATUS_APPROVED);
        })
        ->whereHas('tema', function ($q) {
            $q->where('name', self::ORDER_STATUS_THEME);
        })
        ->first();

        if (!$estado) {
            throw new AprobacionException("Estado 'APROBADA' no encontrado en parámetros.");
        }

        return $estado;
    }

    /**
     * Obtiene estado RECHAZADA
     *
     * @return ParametroTema
     * @throws AprobacionException
     */
    public function obtenerEstadoRechazada(): ParametroTema
    {
        $estado = ParametroTema::whereHas('parametro', function ($q) {
            $q->where('name', self::STATUS_REJECTED);
        })
        ->whereHas('tema', function ($q) {
            $q->where('name', self::ORDER_STATUS_THEME);
        })
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
            DB::beginTransaction();

            $estadoEnEspera = $this->obtenerEstadoEnEspera();
            $estadoAprobada = $this->obtenerEstadoAprobada();

            if (!$estadoEnEspera || $detalleOrden->estado_orden_id != $estadoEnEspera->id) {
                throw new AprobacionException('Esta solicitud no está pendiente de aprobación.');
            }

            if ($detalleOrden->aprobacion) {
                throw new AprobacionException('Esta solicitud ya fue procesada anteriormente.');
            }

            $producto = $detalleOrden->producto;
            if ($producto->cantidad < $detalleOrden->cantidad) {
                throw new AprobacionException(
                    "Stock insuficiente para '{$producto->producto}'. " .
                    "Disponible: {$producto->cantidad}, Solicitado: {$detalleOrden->cantidad}"
                );
            }

            $detalleOrden->update([
                'estado_orden_id' => $estadoAprobada->id,
                'user_update_id' => Auth::id()
            ]);

            $aprobacion = new Aprobacion([
                'detalle_orden_id' => $detalleOrden->id,
                'estado_aprobacion_id' => $estadoAprobada->id,
                'user_create_id' => Auth::id(),
                'user_update_id' => Auth::id()
            ]);
            $aprobacion->save();

            $producto->cantidad -= $detalleOrden->cantidad;
            $producto->user_update_id = Auth::id();
            $producto->save();

            $solicitante = $detalleOrden->orden->userCreate;
            if ($solicitante) {
                $solicitante->notify(new OrdenAprobadaNotification($detalleOrden, Auth::user()));
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
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
            DB::beginTransaction();

            $estadoEnEspera = $this->obtenerEstadoEnEspera();
            $estadoRechazada = $this->obtenerEstadoRechazada();

            if (!$estadoEnEspera || $detalleOrden->estado_orden_id != $estadoEnEspera->id) {
                throw new AprobacionException('Esta solicitud no está pendiente de aprobación.');
            }

            if ($detalleOrden->aprobacion) {
                throw new AprobacionException('Esta solicitud ya fue procesada anteriormente.');
            }

            $detalleOrden->update([
                'estado_orden_id' => $estadoRechazada->id,
                'user_update_id' => Auth::id()
            ]);

            $aprobacion = new Aprobacion([
                'detalle_orden_id' => $detalleOrden->id,
                'estado_aprobacion_id' => $estadoRechazada->id,
                'user_create_id' => Auth::id(),
                'user_update_id' => Auth::id()
            ]);
            $aprobacion->save();

            $orden = $detalleOrden->orden;
            $orden->descripcion_orden .= "\n\n--- SOLICITUD RECHAZADA ---\n";
            $orden->descripcion_orden .= "Producto: {$detalleOrden->producto->producto}\n";
            $orden->descripcion_orden .= "Motivo: {$motivoRechazo}\n";
            $orden->descripcion_orden .= "Rechazado por: " . Auth::user()->name . "\n";
            $orden->descripcion_orden .= "Fecha: " . now()->format('d/m/Y H:i') . "\n";
            $orden->user_update_id = Auth::id();
            $orden->save();

            $solicitante = $detalleOrden->orden->userCreate;
            if ($solicitante) {
                $solicitante->notify(new OrdenRechazadaNotification(
                    $detalleOrden,
                    Auth::user(),
                    $motivoRechazo
                ));
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
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
            DB::beginTransaction();

            $estadoEnEspera = $this->obtenerEstadoEnEspera();
            $estadoAprobada = $this->obtenerEstadoAprobada();

            if (!$estadoEnEspera) {
                throw new AprobacionException("Estado 'EN ESPERA' no encontrado.");
            }

            $detallesPendientes = $orden->detalles->where('estado_orden_id', $estadoEnEspera->id);

            if ($detallesPendientes->isEmpty()) {
                throw new AprobacionException('No hay productos pendientes de aprobación en esta orden.');
            }

            foreach ($detallesPendientes as $detalle) {
                if ($detalle->producto->cantidad < $detalle->cantidad) {
                    throw new AprobacionException(
                        "Stock insuficiente para '{$detalle->producto->producto}'. " .
                        "Disponible: {$detalle->producto->cantidad}, Solicitado: {$detalle->cantidad}"
                    );
                }
            }

            foreach ($detallesPendientes as $detalle) {
                $detalle->update([
                    'estado_orden_id' => $estadoAprobada->id,
                    'user_update_id' => Auth::id()
                ]);

                $aprobacion = new Aprobacion([
                    'detalle_orden_id' => $detalle->id,
                    'estado_aprobacion_id' => $estadoAprobada->id,
                    'user_create_id' => Auth::id(),
                    'user_update_id' => Auth::id()
                ]);
                $aprobacion->save();

                $detalle->producto->cantidad -= $detalle->cantidad;
                $detalle->producto->user_update_id = Auth::id();
                $detalle->producto->save();
            }

            $solicitante = $orden->userCreate;
            if ($solicitante) {
                foreach ($detallesPendientes as $detalle) {
                    $solicitante->notify(new OrdenAprobadaNotification($detalle, Auth::user()));
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
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
            DB::beginTransaction();

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
                $detalle->update([
                    'estado_orden_id' => $estadoRechazada->id,
                    'user_update_id' => Auth::id()
                ]);

                $aprobacion = new Aprobacion([
                    'detalle_orden_id' => $detalle->id,
                    'estado_aprobacion_id' => $estadoRechazada->id,
                    'user_create_id' => Auth::id(),
                    'user_update_id' => Auth::id()
                ]);
                $aprobacion->save();
            }

            $orden->descripcion_orden .= "\n\n--- ORDEN RECHAZADA COMPLETA ---\n";
            $orden->descripcion_orden .= "Motivo: {$motivoRechazo}\n";
            $orden->descripcion_orden .= "Rechazado por: " . Auth::user()->name . "\n";
            $orden->descripcion_orden .= "Fecha: " . now()->format('d/m/Y H:i') . "\n";
            $orden->user_update_id = Auth::id();
            $orden->save();

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

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

