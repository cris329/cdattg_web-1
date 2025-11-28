<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventario;

use App\Inventario\Services\Notification\UserNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;

class NotificacionController extends Controller
{
    protected UserNotificationService $service;

    public function __construct(UserNotificationService $service)
    {
        $this->middleware('can:VER NOTIFICACION')->only(['index']);
        $this->service = $service;
    }

    /**
     * Mostrar todas las notificaciones del usuario
     */
    public function index() : View
    {
        $notificaciones = $this->service->obtenerNotificacionesPaginadas(Auth::id());
        
        return view('inventario.notificaciones.index', compact('notificaciones'));
    }

    /**
     * Obtener notificaciones no leídas para el dropdown
     */
    public function getUnread() : JsonResponse
    {
        $datos = $this->service->obtenerDatosDropdown(Auth::id());
        
        return response()->json($datos);
    }

    /**
     * Marcar una notificación como leída
     */
    public function markAsRead(string $id) : JsonResponse
    {
        $resultado = $this->service->marcarComoLeida(Auth::id(), $id);
        
        if ($resultado) {
            return response()->json([
                'success' => true,
                'message' => 'Notificación marcada como leída'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notificación no encontrada'
        ], 404);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead() : JsonResponse
    {
        $count = $this->service->marcarTodasComoLeidas(Auth::id());
        
        return response()->json([
            'success' => true,
            'message' => "Todas las notificaciones marcadas como leídas ({$count})"
        ]);
    }

    /**
     * Eliminar una notificación
     */
    public function destroy(string $id) : RedirectResponse
    {
        $resultado = $this->service->eliminar(Auth::id(), $id);
        
        if ($resultado) {
            return back()->with('success', 'Notificación eliminada exitosamente');
        }

        return back()->with('error', 'Notificación no encontrada');
    }

    /**
     * Eliminar todas las notificaciones del usuario
     */
    public function destroyAll()  : JsonResponse
    {
        $count = Auth::user()->notifications()->count();

        Auth::user()->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Todas las notificaciones han sido eliminadas',
            'deleted' => $count
        ]);
    }
}
