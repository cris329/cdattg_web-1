<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventario;

use App\Inventario\Interfaces\Repositories\ContratoConvenio\ContratoConvenioRepositoryInterface;
use App\Inventario\Interfaces\Repositories\Proveedor\ProveedorRepositoryInterface;
use App\Inventario\Services\ContratoConvenio\ContratoConvenioService;
use App\Models\Inventario\ContratoConvenio;
use App\Models\Tema;
use App\Exceptions\ContratoConvenioException;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Inventario\ContratoConvenioRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ContratoConvenioController extends Controller
{
    protected ContratoConvenioRepositoryInterface $repository;
    protected ContratoConvenioService $service;
    protected ProveedorRepositoryInterface $proveedorRepository;

    public function __construct(
        ContratoConvenioRepositoryInterface $repository,
        ContratoConvenioService $service,
        ProveedorRepositoryInterface $proveedorRepository
    ) {
        $this->middleware('can:VER CONTRATO')->only('index', 'show');
        $this->middleware('can:CREAR CONTRATO')->only('create', 'store');
        $this->middleware('can:EDITAR CONTRATO')->only('edit', 'update');
        $this->middleware('can:ELIMINAR CONTRATO')->only('destroy');

        $this->repository = $repository;
        $this->service = $service;
        $this->proveedorRepository = $proveedorRepository;
    }

    public function index(Request $request): View
    {
        $filtros = [
            'search' => $request->input('search'),
            'per_page' => 10
        ];

        $contratosConvenios = $this->repository->obtenerConFiltros($filtros);
        $contratosConvenios->appends($request->only('search'));

        // Uso directo del modelo Tema (clase externa, sin SOLID)
        $tema = Tema::where('name', 'ESTADOS')->first();
        $estados = $tema ? collect($tema->parametros()->wherePivot('status', 1)->get()) : collect([]);

        $proveedores = $this->proveedorRepository->obtenerTodos();

        return view('inventario.contratos_convenios.index', compact('contratosConvenios', 'estados', 'proveedores'));
    }

    public function create(): View
    {
        $proveedores = $this->proveedorRepository->obtenerTodos();
        return view('inventario.contratos_convenios.create', compact('proveedores'));
    }

    public function show(ContratoConvenio $contratoConvenio): View
    {
        $contratoConvenio = $this->repository->encontrarConRelaciones($contratoConvenio->id);

        if (!$contratoConvenio) {
            abort(404);
        }

        return view('inventario.contratos_convenios.show', compact('contratoConvenio'));
    }

    public function edit(ContratoConvenio $contratoConvenio): View
    {
        $proveedores = $this->proveedorRepository->obtenerTodos();
        return view('inventario.contratos_convenios.edit', compact('contratoConvenio', 'proveedores'));
    }

    public function store(ContratoConvenioRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $this->service->crear($validated, Auth::id());

            return redirect()
                ->route('inventario.contratos-convenios.index')
                ->with('success', 'Contrato/Convenio creado exitosamente.');
        } catch (ContratoConvenioException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(ContratoConvenioRequest $request, ContratoConvenio $contratoConvenio): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $this->service->actualizar($contratoConvenio, $validated, Auth::id());

            return redirect()
                ->route('inventario.contratos-convenios.index')
                ->with('success', 'Contrato/Convenio actualizado exitosamente.');
        } catch (ContratoConvenioException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(ContratoConvenio $contratoConvenio): RedirectResponse
    {
        try {
            $this->service->eliminar($contratoConvenio);
            return redirect()
                ->route('inventario.contratos-convenios.index')
                ->with('success', 'Contrato/Convenio eliminado exitosamente.');
        } catch (ContratoConvenioException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
