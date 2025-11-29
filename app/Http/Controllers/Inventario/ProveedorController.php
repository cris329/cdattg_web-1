<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventario;

use App\Inventario\Interfaces\Repositories\Proveedor\ProveedorRepositoryInterface;
use App\Inventario\Services\Proveedor\ProveedorService;
use App\Models\Inventario\Proveedor;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Http\Requests\Inventario\ProveedorRequest;
use App\Exceptions\ProveedorException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ProveedorController extends Controller
{
    protected ProveedorRepositoryInterface $repository;
    protected ProveedorService $service;

    public function __construct(
        ProveedorRepositoryInterface $repository,
        ProveedorService $service
    ) {
        $this->middleware('can:VER PROVEEDOR')->only('index', 'show');
        $this->middleware('can:CREAR PROVEEDOR')->only('create', 'store');
        $this->middleware('can:EDITAR PROVEEDOR')->only('edit', 'update');
        $this->middleware('can:ELIMINAR PROVEEDOR')->only('destroy');

        $this->repository = $repository;
        $this->service = $service;
    }

    public function index(Request $request): View
    {
        $filtros = [
            'search' => $request->input('search'),
            'per_page' => 10
        ];

        $proveedores = $this->repository->obtenerConFiltros($filtros);
        $proveedores->appends($request->only('search'));

        return view('inventario.proveedores.index', compact('proveedores'));
    }

    public function create() : View
    {
        $departamentos = Departamento::orderBy('departamento')->get();
        $municipios = Municipio::with('departamento')->get();
        return view('inventario.proveedores.create', compact('departamentos', 'municipios'));
    }

    public function show(Proveedor $proveedor): View
    {
        $proveedor = $this->repository->encontrarConRelaciones($proveedor->id);

        if (!$proveedor) {
            abort(404);
        }

        return view('inventario.proveedores.show', compact('proveedor'));
    }

    public function edit(Proveedor $proveedor) : View
    {
        $departamentos = Departamento::orderBy('departamento')->get();
        $municipios = Municipio::with('departamento')->get();
        return view('inventario.proveedores.edit', compact('proveedor', 'departamentos', 'municipios'));
    }

    public function store(ProveedorRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->crear($validated, Auth::id());

        return redirect()
            ->route('inventario.proveedores.index')
            ->with('success', 'Proveedor creado exitosamente.');
    }

    public function update(ProveedorRequest $request, Proveedor $proveedor): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->actualizar($proveedor, $validated, Auth::id());

        return redirect()
            ->route('inventario.proveedores.index')
            ->with('success', 'Proveedor actualizado exitosamente.');
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        try {
            $this->service->eliminar($proveedor);
            return redirect()
                ->route('inventario.proveedores.index')
                ->with('success', 'Proveedor eliminado exitosamente.');
        } catch (ProveedorException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Obtener municipios por departamento
     */
    public function getMunicipiosPorDepartamento(int $departamentoId): JsonResponse
    {
        $municipios = Municipio::where('departamento_id', $departamentoId)
            ->orderBy('municipio')
            ->get(['id', 'municipio']);

        return response()->json($municipios);
    }
}
