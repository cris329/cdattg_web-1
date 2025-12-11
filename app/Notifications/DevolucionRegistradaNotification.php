<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Devolucion;
use App\Models\Inventario\Producto;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DevolucionRegistradaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Devolucion $devolucion;
    public ?DetalleOrden $detalleOrden;
    public ?Producto $producto;
    public ?User $solicitante;

    public function __construct(Devolucion $devolucion)
    {
        $this->devolucion = $devolucion->loadMissing([
            'detalleOrden',
            'detalleOrden.producto',
            'detalleOrden.orden',
            'detalleOrden.orden.userCreate',
            'detalleOrden.orden.userCreate.persona',
        ]);

        $this->detalleOrden = $this->devolucion->detalleOrden;
        $this->producto = $this->detalleOrden->producto ?? null;
        $this->solicitante = $this->detalleOrden->orden->userCreate ?? null;

        if ($this->solicitante) {
            $this->solicitante->loadMissing(['persona', 'roles']);
        }
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $cantidadDevuelta = $this->devolucion->cantidad_devuelta;
        $fechaDevolucion = $this->devolucion->fecha_devolucion?->format('d/m/Y H:i') ?? 'N/A';

        return (new MailMessage())
            ->subject("Devolución registrada • Orden #{$this->detalleOrden->orden->id}")
            ->view('inventario.email.devolucion-registrada', [
                'notifiable' => $notifiable,
                'devolucion' => $this->devolucion,
                'detalleOrden' => $this->detalleOrden,
                'producto' => $this->producto,
                'cantidadDevuelta' => $cantidadDevuelta,
                'fechaDevolucion' => $fechaDevolucion,
                'solicitante' => $this->solicitante,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $persona = $this->solicitante?->persona;

        return [
            'tipo' => 'devolucion_registrada',
            'devolucion_id' => $this->devolucion->id,
            'orden_id' => $this->detalleOrden?->orden->id,
            'detalle_orden_id' => $this->detalleOrden?->id,
            'producto_id' => $this->producto?->id,
            'producto_nombre' => $this->producto?->name,
            'cantidad_devuelta' => $this->devolucion->cantidad_devuelta,
            'cierra_sin_stock' => $this->devolucion->cierra_sin_stock,
            'observaciones' => $this->devolucion->observaciones,
            'fecha_devolucion' => $this->devolucion->fecha_devolucion?->toDateTimeString(),
            'usuario' => [
                'id' => $this->solicitante?->id,
                'name' => $this->solicitante?->name,
                'email' => $this->solicitante?->email,
                'documento' => $persona?->numero_documento,
            ],
            'icon' => 'fas fa-undo',
            'color' => 'success',
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'devolucion_registrada';
    }
}

