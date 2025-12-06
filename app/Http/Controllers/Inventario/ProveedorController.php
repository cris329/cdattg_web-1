<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventario;

use App\Inventario\Interfaces\Repositories\Proveedor\ProveedorRepositoryInterface;
use App\Inventario\Services\Proveedor\ProveedorService;
use App\Models\Inventario\Proveedor;
use App\Models\Pais;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Persona;
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
        $paises = Pais::where('status', 1)->orderBy('pais')->get();
        $departamentos = Departamento::orderBy('departamento')->get();
        $municipios = Municipio::with('departamento')->get();
        $personas = Persona::where('status', 1)
            ->whereHas('user', function ($query) {
                $query->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'PROVEEDOR');
                });
            })
            ->orderBy('primer_nombre')
            ->orderBy('primer_apellido')
            ->get();
        return view('inventario.proveedores.create', compact('paises', 'departamentos', 'municipios', 'personas'));
    }

    public function show(Proveedor $proveedor): View
    {
        $proveedor = $this->repository->encontrarConRelaciones($proveedor->id);
        return view('inventario.proveedores.show', compact('proveedor'));
    }

    public function edit(Proveedor $proveedor) : View
    {
        $paises = Pais::where('status', 1)->orderBy('pais')->get();
        $departamentos = Departamento::orderBy('departamento')->get();
        $municipios = Municipio::with('departamento')->get();
        
        // Obtener personas con rol PROVEEDOR, incluyendo la persona actualmente asignada si existe
        $personas = Persona::where('status', 1)
            ->where(function ($query) use ($proveedor) {
                $query->whereHas('user', function ($userQuery) {
                    $userQuery->whereHas('roles', function ($roleQuery) {
                        $roleQuery->where('name', 'PROVEEDOR');
                    });
                });
                
                // Incluir la persona actualmente asignada al proveedor (si existe)
                if ($proveedor->persona_id) {
                    $query->orWhere('id', $proveedor->persona_id);
                }
            })
            ->orderBy('primer_nombre')
            ->orderBy('primer_apellido')
            ->get();
            
        return view('inventario.proveedores.edit', compact('proveedor', 'paises', 'departamentos', 'municipios', 'personas'));
    }

    public function store(ProveedorRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $this->service->crear($validated, Auth::id());

            return redirect()
                ->route('inventario.proveedores.index')
                ->with('success', 'Proveedor creado exitosamente.');
        } catch (ProveedorException $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al crear el proveedor: ' . $e->getMessage());
        }
    }

    public function update(ProveedorRequest $request, Proveedor $proveedor): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $this->service->actualizar($proveedor, $validated, Auth::id());

            return redirect()
                ->route('inventario.proveedores.index')
                ->with('success', 'Proveedor actualizado exitosamente.');
        } catch (ProveedorException $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el proveedor: ' . $e->getMessage());
        }
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
     * Obtener departamentos por país
     */
    public function getDepartamentosPorPais(int $paisId): JsonResponse
    {
        try {
            $departamentos = Departamento::where('pais_id', $paisId)
                ->orderBy('departamento')
                ->get(['id', 'departamento']);

            return response()->json($departamentos);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener los departamentos',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener municipios por departamento
     */
    public function getMunicipiosPorDepartamento(int $departamentoId): JsonResponse
    {
        try {
            $municipios = Municipio::where('departamento_id', $departamentoId)
                ->orderBy('municipio')
                ->get(['id', 'municipio']);

            return response()->json($municipios);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener los municipios',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
