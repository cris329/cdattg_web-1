<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventario;

use App\Inventario\Services\Aprobacion\AprobacionService;
use App\Exceptions\AprobacionException;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Inventario\AprobacionesRequest;
use App\Http\Controllers\Controller;

class AprobacionController extends Controller
{
    public function __construct(
        private readonly AprobacionService $service
    ) {
        $this->middleware('can:APROBAR ORDEN')
            ->only(['aprobar', 'rechazar', 'pendientes', 'aprobarOrden', 'rechazarOrden']);
    }

    /**
     * Manejo centralizado de excepciones de aprobación
     */
    private function handleAprobacion(callable $callback): RedirectResponse
    {
        try {
            return $callback();
        } catch (AprobacionException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mostrar órdenes pendientes
     */
    public function pendientes(): View
    {
        $detalles = $this->service->obtenerDetallesPendientes();
        
        // Asegurar que siempre sea una colección
        if (!$detalles || !($detalles instanceof \Illuminate\Support\Collection)) {
            $detalles = collect([]);
        }
        
        return view('inventario.aprobaciones.pendientes', compact('detalles'));
    }

    /**
     * Aprobar un detalle de orden
     */
    public function aprobar(int $detalleOrdenId): RedirectResponse
    {
        return $this->handleAprobacion(function () use ($detalleOrdenId) {
            $detalleOrden = $this->service->encontrarDetalleConRelaciones($detalleOrdenId);

            $this->service->aprobarDetalle($detalleOrden);

            return back()->with(
                'success',
                "Solicitud aprobada. Stock actualizado para '{$detalleOrden->producto->name}'."
            );
        });
    }

    /**
     * Rechazar un detalle de orden
     */
    public function rechazar(AprobacionesRequest $request, int $detalleOrdenId): RedirectResponse
    {
        return $this->handleAprobacion(function () use ($request, $detalleOrdenId) {

            $validated = $request->validated();

            $detalleOrden = $this->service->encontrarDetalleConRelaciones($detalleOrdenId);

            $this->service->rechazarDetalle($detalleOrden, $validated['motivo_rechazo']);

            return back()->with('success', 'Solicitud rechazada exitosamente.');
        });
    }

    /**
     * Aprobar una orden completa
     */
    public function aprobarOrden(int $ordenId): RedirectResponse
    {
        return $this->handleAprobacion(function () use ($ordenId) {

            $orden = $this->service->encontrarOrdenConDetallesYDevoluciones($ordenId);

            $this->service->aprobarOrdenCompleta($orden);

            return back()->with(
                'success',
                "Orden #{$ordenId} aprobada exitosamente. Stock actualizado para todos los productos."
            );
        });
    }

    /**
     * Rechazar una orden completa
     */
    public function rechazarOrden(AprobacionesRequest $request, int $ordenId): RedirectResponse
    {
        return $this->handleAprobacion(function () use ($request, $ordenId) {

            $validated = $request->validated();

            $orden = $this->service->encontrarOrdenConDetallesYDevoluciones($ordenId);

            $this->service->rechazarOrdenCompleta($orden, $validated['motivo_rechazo']);

            return back()->with(
                'success',
                "Orden #{$ordenId} rechazada exitosamente."
            );
        });
    }
}
