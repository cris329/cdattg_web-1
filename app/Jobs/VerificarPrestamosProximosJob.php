<?php

namespace App\Jobs;

use App\Models\Inventario\Orden;
use App\Models\ParametroTema;
use App\Models\Tema;
use App\Models\Parametro;
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
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $diasAntes = 3;
            $fechaObjetivo = Carbon::today()->addDays($diasAntes);

            // Obtener IDs necesarios
            $tipoPrestamoId = $this->getTipoPrestamoId();
            $estadoAprobadaId = $this->getEstadoAprobadaId();

            if (!$tipoPrestamoId || !$estadoAprobadaId) {
                Log::warning('[VerificarPrestamosProximosJob] No se encontraron tipos o estados necesarios');
                return;
            }

            // Buscar préstamos que vencen en 3 días
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

            Log::info('[VerificarPrestamosProximosJob] Verificación completada', [
                'ordenes_procesadas' => $ordenes->count(),
                'fecha_objetivo' => $fechaObjetivo->format('Y-m-d'),
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

            // Verificar si ya se envió notificación hoy
            if ($this->yaSeEnvioNotificacionHoy($orden)) {
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
     * Obtener el ID del tipo de orden PRÉSTAMO
     */
    private function getTipoPrestamoId(): ?int
    {
        $tema = Tema::where('name', 'TIPOS DE ORDEN')->first();
        if (!$tema) {
            return null;
        }

        $parametro = Parametro::where('name', 'PRÉSTAMO')->first();
        if (!$parametro) {
            return null;
        }

        $parametroTema = ParametroTema::where('tema_id', $tema->id)
            ->where('parametro_id', $parametro->id)
            ->where('status', 1)
            ->first();

        return $parametroTema?->id;
    }

    /**
     * Obtener el ID del estado APROBADA
     */
    private function getEstadoAprobadaId(): ?int
    {
        $tema = Tema::where('name', 'ESTADOS DE ORDEN')->first();
        if (!$tema) {
            return null;
        }

        $parametro = Parametro::where('name', 'APROBADA')->first();
        if (!$parametro) {
            return null;
        }

        $parametroTema = ParametroTema::where('tema_id', $tema->id)
            ->where('parametro_id', $parametro->id)
            ->where('status', 1)
            ->first();

        return $parametroTema?->id;
    }

    /**
     * Verificar si ya se envió notificación hoy para esta orden
     */
    private function yaSeEnvioNotificacionHoy(Orden $orden): bool
    {
        if (!$orden->userCreate) {
            return false;
        }

        return DB::table('notifications')
            ->where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $orden->userCreate->id)
            ->where('type', RecordatorioDevolucionNotification::class)
            ->whereDate('created_at', Carbon::today())
            ->whereRaw("JSON_EXTRACT(data, '$.orden_id') = ?", [$orden->id])
            ->exists();
    }
}
