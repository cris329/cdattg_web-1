<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockBajoNotification extends Notification
{
    use Queueable;

    public $producto;
    public $stockActual;
    public $stockMinimo;

    /**
     * Create a new notification instance.
     */
    public function __construct($producto, $stockActual, $stockMinimo = 10)
    {
        $this->producto = $producto;
        $this->stockActual = $stockActual;
        $this->stockMinimo = $stockMinimo;
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
        return (new MailMessage)
            ->subject('⚠️ Alerta de Stock Bajo - ' . $this->producto->name)
            ->view('inventario.email.stock-bajo', [
                'notifiable' => $notifiable,
                'producto' => $this->producto,
                'stockActual' => $this->stockActual,
                'stockMinimo' => $this->stockMinimo,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'producto_id' => $this->producto->id,
            'producto_nombre' => $this->producto->name,
            'stock_actual' => $this->stockActual,
            'stock_minimo' => $this->stockMinimo,
            'tipo' => 'stock_bajo',
            'nivel_alerta' => $this->stockActual == 0 ? 'critico' : 'bajo',
        ];
    }
}
