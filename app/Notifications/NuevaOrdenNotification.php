<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NuevaOrdenNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $orden;
    public $solicitante;

    /**
     * Create a new notification instance.
     */
    public function __construct($orden)
    {
        $this->orden = $orden;
        // Cargar relaciones necesarias
        $this->solicitante = $orden->userCreate;
        if ($this->solicitante) {
            $this->solicitante->load('persona', 'roles');
        }
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
        $tipoOrden = $this->orden->tipoOrden->parametro->name ?? 'N/A';
        $cantidadProductos = $this->orden->detalles->count();

        // Extraer motivo de la descripción
        $descripcion = $this->orden->descripcion_orden ?? '';
        preg_match('/MOTIVO:\s*(.+)$/s', $descripcion, $matchMotivo);
        $motivo = isset($matchMotivo[1]) ? trim($matchMotivo[1]) : 'No especificado';

        return (new MailMessage)
            ->subject('Nueva Solicitud de ' . $tipoOrden . ' - Orden #' . $this->orden->id)
            ->view('inventario.email.nueva-orden', [
                'notifiable' => $notifiable,
                'orden' => $this->orden,
                'tipoOrden' => $tipoOrden,
                'solicitante' => $this->solicitante,
                'cantidadProductos' => $cantidadProductos,
                'motivo' => $motivo,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $tipoOrden = $this->orden->tipoOrden->parametro->name ?? 'N/A';
        $solicitante = $this->solicitante;

        // Obtener el rol del usuario
        $rol = $solicitante && $solicitante->roles->isNotEmpty()
            ? $solicitante->roles->first()->name
            : 'N/A';

        // Obtener datos de la persona
        $persona = $solicitante ? $solicitante->persona : null;
        $nombreCompleto = $persona
            ? trim(($persona->primer_nombre ?? '') . ' ' . ($persona->segundo_nombre ?? '') . ' ' .
                   ($persona->primer_apellido ?? '') . ' ' . ($persona->segundo_apellido ?? ''))
            : ($solicitante->name ?? 'N/A');

        $documento = $persona ? $persona->numero_documento ?? 'N/A' : 'N/A';

        return [
            'orden_id' => $this->orden->id,
            'tipo_orden' => $tipoOrden,
            'solicitante' => [
                'id' => $solicitante->id ?? null,
                'name' => $nombreCompleto,
                'email' => $solicitante->email ?? 'N/A',
                'documento' => $documento,
                'rol' => $rol,
            ],
            'cantidad_productos' => $this->orden->detalles->count(),
            'tipo' => 'nueva_orden',
        ];
    }
}
