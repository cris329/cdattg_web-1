<?php

namespace App\Jobs;

use App\Inventario\Interfaces\Repositories\ParametroTema\ParametroTemaRepositoryInterface;
use App\Models\Inventario\Orden;
use App\Notifications\RecordatorioDevolucionNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificarPrestamosProximosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ParametroTemaRepositoryInterface $parametroTemaRepository): void
    {
        try {
            // Obtener IDs necesarios usando el repositorio
            $tipoPrestamoId = $this->obtenerTipoPrestamoId($parametroTemaRepository);
            $estadoAprobadaId = $this->obtenerEstadoAprobadaId($parametroTemaRepository);

            if (!$tipoPrestamoId || !$estadoAprobadaId) {
                Log::warning('[VerificarPrestamosProximosJob] No se encontraron tipos o estados necesarios');
                return;
            }

            $ordenesTotales = 0;

            // Verificar para 3, 2 y 1 días antes del vencimiento
            foreach ([3, 2, 1] as $diasAntes) {
                $fechaObjetivo = Carbon::today()->addDays($diasAntes);

                // Buscar préstamos que vencen en N días
                $ordenes = Orden::with(['detalles.producto', 'detalles.devoluciones', 'userCreate'])
                    ->where('tipo_orden_id', $tipoPrestamoId)
                    ->whereDate('fecha_devolucion', $fechaObjetivo)
                    ->whereNotNull('fecha_devolucion')
                    ->whereHas('detalles', function ($query) use ($estadoAprobadaId) {
                        $query->where('estado_orden_id', $estadoAprobadaId);
                    })
                    ->get();

                foreach ($ordenes as $orden) {
                    $this->procesarOrden($orden, $diasAntes);
                }

                $ordenesTotales += $ordenes->count();

                Log::info('[VerificarPrestamosProximosJob] Verificación para ' . $diasAntes . ' días', [
                    'ordenes_procesadas' => $ordenes->count(),
                    'fecha_objetivo' => $fechaObjetivo->format('Y-m-d'),
                    'dias_restantes' => $diasAntes,
                ]);
            }

            Log::info('[VerificarPrestamosProximosJob] Verificación completada', [
                'ordenes_totales' => $ordenesTotales,
            ]);

        } catch (\Exception $e) {
            Log::error('[VerificarPrestamosProximosJob] Error en la verificación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Procesar una orden individual
     */
    private function procesarOrden(Orden $orden, int $diasAntes): void
    {
        try {
            // Verificar que tenga productos pendientes
            $tienePendientes = false;
            foreach ($orden->detalles as $detalle) {
                if ($detalle->getCantidadPendiente() > 0) {
                    $tienePendientes = true;
                    break;
                }
            }

            if (!$tienePendientes) {
                return;
            }

            // Verificar que tenga usuario
            if (!$orden->userCreate) {
                return;
            }

            // Verificar si ya se envió notificación hoy para estos días específicos
            if ($this->yaSeEnvioNotificacionHoy($orden, $diasAntes)) {
                return;
            }

            // Enviar notificación
            $orden->userCreate->notify(new RecordatorioDevolucionNotification($orden, $diasAntes));

            Log::info('[VerificarPrestamosProximosJob] Notificación enviada', [
                'orden_id' => $orden->id,
                'usuario_id' => $orden->userCreate->id,
                'usuario_email' => $orden->userCreate->email,
                'fecha_devolucion' => $orden->fecha_devolucion->format('Y-m-d'),
                'dias_restantes' => $diasAntes,
            ]);

        } catch (\Exception $e) {
            Log::error('[VerificarPrestamosProximosJob] Error al procesar orden', [
                'orden_id' => $orden->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Obtener el ID del tipo de orden PRÉSTAMO usando el repositorio
     */
    private function obtenerTipoPrestamoId(ParametroTemaRepositoryInterface $parametroTemaRepository): ?int
    {
        $parametroTema = $parametroTemaRepository->obtenerEstadoPorNombre('PRÉSTAMO', 'TIPOS DE ORDEN');
        return $parametroTema?->id;
    }

    /**
     * Obtener el ID del estado APROBADA usando el repositorio
     */
    private function obtenerEstadoAprobadaId(ParametroTemaRepositoryInterface $parametroTemaRepository): ?int
    {
        $parametroTema = $parametroTemaRepository->obtenerEstadoPorNombre('APROBADA', 'ESTADOS DE ORDEN');
        return $parametroTema?->id;
    }

    /**
     * Verificar si ya se envió notificación hoy para esta orden y días específicos
     */
    private function yaSeEnvioNotificacionHoy(Orden $orden, int $diasAntes): bool
    {
        if (!$orden->userCreate) {
            return false;
        }

        try {
            return DB::table('notificaciones')
                ->where('notificable_type', 'App\\Models\\User')
                ->where('notificable_id', $orden->userCreate->id)
                ->where('tipo', 'App\\Notifications\\RecordatorioDevolucionNotification')
                ->whereDate('created_at', Carbon::today())
                ->whereRaw("JSON_EXTRACT(datos, '$.orden_id') = ?", [$orden->id])
                ->whereRaw("JSON_EXTRACT(datos, '$.dias_restantes') = ?", [$diasAntes])
                ->exists();
        } catch (\Exception $e) {
            Log::warning('[VerificarPrestamosProximosJob] Error al verificar duplicados', [
                'error' => $e->getMessage(),
                'orden_id' => $orden->id,
                'dias_antes' => $diasAntes
            ]);
            // Si hay error en la verificación, permitir envío para no bloquear notificaciones
            return false;
        }
    }
}
