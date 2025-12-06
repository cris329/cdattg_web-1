<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventario;

use App\Inventario\Interfaces\Repositories\Orden\OrdenRepositoryInterface;
use App\Inventario\Services\Orden\OrdenService;
use App\Models\ProgramaFormacion;
use Illuminate\Http\Request;
use App\Exceptions\OrdenException;
use App\Models\Inventario\Orden;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Inventario\OrdenRequest;
use App\Http\Controllers\Controller;

class OrdenController extends Controller
{
    protected OrdenRepositoryInterface $repository;
    protected OrdenService $service;

    public function __construct(
        OrdenRepositoryInterface $repository,
        OrdenService $service
    ) {
        $this->middleware('can:VER ORDEN')->only(['index', 'show', 'prestamosSalidas']);
        $this->middleware('can:CREAR ORDEN')->only(['store', 'storePrestamos']);
        $this->middleware('can:EDITAR ORDEN')->only(['update']);
        $this->middleware('can:ELIMINAR ORDEN')->only(['destroy']);
        $this->middleware('can:APROBAR ORDEN')->only(['aprobar']);
        $this->middleware('can:COMPLETAR ORDEN')->only(['completar']);

        $this->repository = $repository;
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filtros = [
            'search' => $request->input('search'),
            'per_page' => 15
        ];

        $ordenes = $this->repository->obtenerConFiltros($filtros);
        $ordenes->appends($request->only('search'));

        return view('inventario.ordenes.index', compact('ordenes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrdenRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $this->service->crear($validated, Auth::id());

            return redirect()
                ->route('inventario.ordenes.index')
                ->with('success', 'Orden creada exitosamente. Stock actualizado.');

        } catch (OrdenException $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al crear la orden: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de solicitud de préstamo/salida
     */
    public function prestamosSalidas() : View
    {
        $programas = ProgramaFormacion::where('status', true)
            ->orderBy('nombre', 'asc')
            ->get(['id', 'nombre', 'codigo']);

        return view('inventario.ordenes.prestamos_salidas', compact('programas'));
    }

    /**
     * Mostrar órdenes pendientes (EN ESPERA)
     */
    public function pendientes(): View
    {
        try {
            $estadoEnEspera = $this->service->obtenerEstadoEnEspera();
            $ordenes = $this->repository->obtenerPendientes($estadoEnEspera->id);
        } catch (OrdenException $e) {
            $ordenes = collect();
        }

        return view('inventario.ordenes.pendientes', compact('ordenes'));
    }

    /**
     * Mostrar órdenes completadas (APROBADA)
     */
    public function completadas(): View
    {
        try {
            $estadoAprobada = $this->service->obtenerEstadoAprobada();
            $ordenes = $this->repository->obtenerCompletadas($estadoAprobada->id);
        } catch (OrdenException $e) {
            $ordenes = collect();
        }

        return view('inventario.ordenes.completadas', compact('ordenes'));
    }

    /**
     * Mostrar órdenes rechazadas (RECHAZADA)
     */
    public function rechazadas(): View
    {
        // Obtener estado RECHAZADA desde AprobacionService
        $estadoRechazada = app(\App\Inventario\Services\Aprobacion\AprobacionService::class)->obtenerEstadoRechazada();
        $ordenes = $this->repository->obtenerRechazadas($estadoRechazada->id);

        return view('inventario.ordenes.rechazadas', compact('ordenes'));
    }

    /**
     * Store a newly created resource in storage (Préstamos y Salidas).
     */
    public function storePrestamos(OrdenRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $this->service->crearDesdeCarrito($validated, Auth::id());

            Session::forget('carrito_data');

            return redirect()
                ->route('inventario.productos.catalogo')
                ->with('success', 'Solicitud creada exitosamente. Está pendiente de aprobación por el administrador.')
                ->with('clear_cart', true);

        } catch (OrdenException $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al crear la solicitud: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrdenRequest $request, string $id): RedirectResponse
    {
        $orden = $this->repository->encontrarConDetallesYDevoluciones((int) $id);

        if (!$orden) {
            abort(404);
        }

        if ($this->service->tieneDevoluciones($orden)) {
            return redirect()
                ->route('inventario.ordenes.index', $orden->id)
                ->with('error', 'No se puede editar una orden que ya tiene devoluciones registradas.');
        }

        try {
            $validated = $request->validated();
            $this->service->actualizar($orden, $validated, Auth::id());

            return redirect()
                ->route('inventario.ordenes.index', $orden->id)
                ->with('success', 'Orden actualizada exitosamente. Stock actualizado.');

        } catch (OrdenException $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la orden: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $orden = $this->repository->encontrarConDetallesYDevoluciones((int) $id);

        if (!$orden) {
            abort(404);
        }

        if ($this->service->tieneDevoluciones($orden)) {
            return redirect()
                ->route('inventario.ordenes.index')
                ->with('error', 'No se puede eliminar una orden que ya tiene devoluciones registradas.');
        }

        try {
            $this->service->eliminar($orden);

            return redirect()
                ->route('inventario.ordenes.index')
                ->with('success', 'Orden eliminada exitosamente. Stock restaurado.');

        } catch (OrdenException $e) {
            return redirect()
                ->route('inventario.ordenes.index')
                ->with('error', 'Error al eliminar la orden: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Orden $orden): View
    {
        $orden = $this->repository->encontrarConRelaciones($orden->id);

        if (!$orden) {
            abort(404);
        }

        $backUrl = request()->get('ref') 
            ?? url()->previous() 
            ?? route('inventario.ordenes.index');

        return view('inventario.ordenes.show', compact('orden', 'backUrl'));
    }
}
