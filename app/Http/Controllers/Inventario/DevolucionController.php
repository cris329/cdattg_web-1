<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventario;

use App\Inventario\Interfaces\Repositories\Devolucion\DevolucionRepositoryInterface;
use App\Inventario\Interfaces\Repositories\Orden\DetalleOrdenRepositoryInterface;
use App\Inventario\Services\Devolucion\DevolucionService;
use App\Exceptions\DevolucionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Inventario\DevolucionRequest;
use App\Http\Controllers\Controller;

class DevolucionController extends Controller
{
    protected DevolucionRepositoryInterface $repository;
    protected DetalleOrdenRepositoryInterface $detalleOrdenRepository;
    protected DevolucionService $service;

    public function __construct(
        DevolucionRepositoryInterface $repository,
        DetalleOrdenRepositoryInterface $detalleOrdenRepository,
        DevolucionService $service
    ) {
        $this->middleware('can:DEVOLVER PRESTAMO')->only(['index', 'create', 'store']);

        $this->repository = $repository;
        $this->detalleOrdenRepository = $detalleOrdenRepository;
        $this->service = $service;
    }

    // Mostrar lista de préstamos pendientes de devolución
    public function index(): View
    {
        try {
            $estadoAprobadaId = $this->getEstadoOrdenAprobadaId();
            $user = Auth::user();
            $userId = null;

            if ($user !== null && !$user->can('VER TODAS LAS ORDENES')) {
                $userId = (int) $user->id;
            }

            $prestamos = $this->repository->obtenerPrestamosPendientes($estadoAprobadaId, $userId);

            return view('inventario.devoluciones.index', compact('prestamos'));
        } catch (\Exception $e) {
            Log::error('Error en DevolucionController@index: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }


    // Mostrar formulario de devolución
    public function create(int $detalleOrdenId): View|RedirectResponse
    {
        try {
            $detalleOrden = $this->detalleOrdenRepository->encontrarConRelaciones($detalleOrdenId);

            if (!$detalleOrden) {
                abort(404);
            }

            $user = Auth::user();

            if ($user !== null
                && !$user->can('VER TODAS LAS ORDENES')
                && (int) $detalleOrden->orden->user_create_id !== (int) $user->id
            ) {
                abort(403);
            }

            if ($detalleOrden->estaCompletamenteDevuelto()) {
                return redirect()
                    ->route('inventario.devoluciones.index')
                    ->with('error', 'Este préstamo ya fue completamente devuelto.');
            }

            return view('inventario.devoluciones.create', compact('detalleOrden'));
        } catch (\Exception $e) {
            Log::error('Error en DevolucionController@create: ' . $e->getMessage(), [
                'exception' => $e,
                'detalle_orden_id' => $detalleOrdenId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }


    // Registrar devolución
    public function store(DevolucionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ((int) $validated['cantidad_devuelta'] === 0) {
            $observaciones = $validated['observaciones'] ?? '';
            if (trim($observaciones) === '') {
                throw ValidationException::withMessages([
                    'observaciones' => 'Debes indicar el motivo cuando registras una devolución de cantidad cero.',
                ]);
            }
        }

        try {
            $resultado = $this->service->registrarDevolucionConMensaje(
                (int) $validated['detalle_orden_id'],
                (int) $validated['cantidad_devuelta'],
                $validated['observaciones'] ?? null
            );

            return redirect()
                ->route('inventario.devoluciones.index')
                ->with('success', $resultado['mensaje']);

        } catch (DevolucionException $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al registrar la devolución: ' . $e->getMessage());
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error inesperado al registrar la devolución: ' . $e->getMessage());
        }
    }

    // Mostrar historial de devoluciones
    public function historial(): View
    {
        $user = Auth::user();
        $userId = null;

        if ($user !== null && !$user->can('VER TODAS LAS ORDENES')) {
            $userId = (int) $user->id;
        }

        $devoluciones = $this->repository->obtenerHistorial($userId);

        return view('inventario.devoluciones.historial', compact('devoluciones'));
    }

    // Ver detalle de una devolución
    public function show(int $id): View
    {
        $devolucion = $this->repository->encontrarConRelaciones($id);

        if (!$devolucion) {
            abort(404);
        }

        $user = Auth::user();

        if ($user !== null
            && !$user->can('VER TODAS LAS ORDENES')
            && (int) $devolucion->detalleOrden->orden->user_create_id !== (int) $user->id
        ) {
            abort(403);
        }

        return view('inventario.devoluciones.show', compact('devolucion'));
    }
    // Mostrar préstamos activos del usuario actual
    public function misPrestamos(): View
    {
        $userId = Auth::id();
        $estadoAprobadaId = $this->getEstadoOrdenAprobadaId();
        $prestamos = $this->repository->obtenerPrestamosActivosUsuario($userId, $estadoAprobadaId);

        return view('inventario.prestamos.usuariosPrestamos', compact('prestamos'));
    }

    // Historial de préstamos del usuario
    public function historialPrestamos(): View
    {
        $userId = Auth::id();
        $prestamos = $this->repository->obtenerHistorialPrestamosUsuario($userId);

        return view('inventario.prestamos.historial', compact('prestamos'));
    }

    private function getEstadoOrdenAprobadaId(): int
    {
        $estadoAprobada = $this->service->obtenerEstadoAprobada();
        return (int) $estadoAprobada->id;
    }
}
