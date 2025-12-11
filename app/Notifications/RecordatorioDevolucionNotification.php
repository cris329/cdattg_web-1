<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecordatorioDevolucionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $orden;
    public $diasRestantes;

    /**
     * Create a new notification instance.
     */
    public function __construct($orden, int $diasRestantes = 3)
    {
        $this->orden = $orden;
        $this->diasRestantes = $diasRestantes;
        // Cargar relaciones necesarias
        $this->orden->load(['detalles.producto', 'tipoOrden.parametro']);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $fechaDevolucion = $this->orden->fecha_devolucion->format('d/m/Y');
        $cantidadProductos = $this->orden->detalles->count();
        
        $diasTexto = $this->diasRestantes === 1 
            ? '1 día' 
            : $this->diasRestantes . ' días';
        
        return (new MailMessage)
            ->subject('Recordatorio: Devolución de Préstamo en ' . $diasTexto)
            ->view('inventario.email.recordatorio-devolucion', [
                'notifiable' => $notifiable,
                'orden' => $this->orden,
                'fechaDevolucion' => $fechaDevolucion,
                'cantidadProductos' => $cantidadProductos,
                'diasRestantes' => $this->diasRestantes,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $fechaDevolucion = $this->orden->fecha_devolucion->format('d/m/Y');
        $cantidadProductos = $this->orden->detalles->count();
        
        // Obtener productos pendientes
        $productosPendientes = [];
        foreach ($this->orden->detalles as $detalle) {
            $cantidadPendiente = $detalle->getCantidadPendiente();
            if ($cantidadPendiente > 0) {
                $productosPendientes[] = [
                    'id' => $detalle->producto->id,
                    'nombre' => $detalle->producto->name,
                    'cantidad_pendiente' => $cantidadPendiente,
                ];
            }
        }

        return [
            'tipo' => 'recordatorio_devolucion',
            'titulo' => 'Recordatorio de Devolución',
            'mensaje' => "Tu préstamo vence en {$this->diasRestantes} " . ($this->diasRestantes === 1 ? 'día' : 'días'),
            'orden_id' => $this->orden->id,
            'fecha_devolucion' => $fechaDevolucion,
            'dias_restantes' => $this->diasRestantes,
            'cantidad_productos' => $cantidadProductos,
            'productos_pendientes' => $productosPendientes,
            'url' => '/inventario/mis-prestamos',
            'icon' => 'fas fa-clock',
            'color' => 'warning',
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'recordatorio_devolucion';
    }
}
