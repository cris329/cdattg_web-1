<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrdenAprobadaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $detalleOrden;
    public $aprobador;

    /**
     * Create a new notification instance.
     */
    public function __construct($detalleOrden, $aprobador)
    {
        $this->detalleOrden = $detalleOrden;
        $this->aprobador = $aprobador;
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
        $orden = $this->detalleOrden->orden;
        $producto = $this->detalleOrden->producto;
        $tipoOrden = $orden->tipoOrden->parametro->name ?? 'N/A';

        return (new MailMessage)
            ->subject('Tu Solicitud ha sido Aprobada - Orden #' . $orden->id)
            ->view('inventario.email.orden-aprobada', [
                'notifiable' => $notifiable,
                'orden' => $orden,
                'detalleOrden' => $this->detalleOrden,
                'producto' => $producto,
                'tipoOrden' => $tipoOrden,
                'aprobador' => $this->aprobador,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $producto = $this->detalleOrden->producto;

        return [
            'orden_id' => $this->detalleOrden->orden->id,
            'detalle_orden_id' => $this->detalleOrden->id,
            'producto' => [
                'id' => $producto->id,
                'producto' => $producto->name,
                'name' => $producto->name,
            ],
            'cantidad' => $this->detalleOrden->cantidad,
            'aprobador' => [
                'id' => $this->aprobador->id,
                'name' => $this->aprobador->name,
            ],
            'tipo' => 'orden_aprobada',
        ];
    }
}
