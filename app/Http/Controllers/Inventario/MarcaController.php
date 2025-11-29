<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventario;

use App\Inventario\Interfaces\Repositories\Marca\MarcaRepositoryInterface;
use App\Inventario\Services\Marca\MarcaService;
use App\Models\Inventario\Marca;
use App\Models\Parametro;
use App\Exceptions\MarcaException;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Inventario\MarcaCategoriaRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class MarcaController extends Controller
{
    protected MarcaRepositoryInterface $repository;
    protected MarcaService $service;

    public function __construct(
        MarcaRepositoryInterface $repository,
        MarcaService $service
    ) {

        $this->middleware('can:VER MARCA')->only('index', 'show');
        $this->middleware('can:CREAR MARCA')->only('create', 'store');
        $this->middleware('can:EDITAR MARCA')->only('edit', 'update');
        $this->middleware('can:ELIMINAR MARCA')->only('destroy');

        $this->repository = $repository;
        $this->service = $service;
    }

    public function index(Request $request): View|RedirectResponse
    {
        $temaMarcas = $this->repository->obtenerTemaMarcas();

        if (!$temaMarcas) {
            return back()->with('error', 'No existe el tema "MARCAS" en la base de datos.');
        }

        $filtros = [
            'search' => $request->input('search'),
            'per_page' => 10
        ];

        $marcas = $this->repository->obtenerConFiltros($filtros);
        $marcas->appends($request->only('search'));

        return view('inventario.marcas.index', compact('marcas'));
    }

    public function create() : View
    {
        return view('inventario.marcas.create');
    }


    public function store(MarcaCategoriaRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $this->service->crear($validated, Auth::id());

            return redirect()
                ->route('inventario.marcas.index')
                ->with('success', 'Marca creada exitosamente.');
        } catch (MarcaException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit(Parametro $marca) : View
    {
        return view('inventario.marcas.edit', [
            'title' => 'Editar marca',
            'icon' => 'fas fa-tag',
            'action' => route('inventario.marcas.update', $marca->id),
            'method' => 'PUT',
            'submitText' => 'Actualizar marca',
            'cancelRoute' => route('inventario.marcas.index'),
            'marca' => $marca
        ]);
    }


    public function update(MarcaCategoriaRequest $request, Parametro $marca): RedirectResponse
    {
        $validated = $request->validated();
        $marcaModel = $this->repository->encontrar($marca->id);

        if (!$marcaModel) {
            abort(404);
        }

        $this->service->actualizar($marcaModel, $validated, Auth::id());

        return redirect()
            ->route('inventario.marcas.index')
            ->with('success', 'Marca actualizada exitosamente.');
    }

    public function destroy(Parametro $marca): RedirectResponse
    {
        try {
            $marcaModel = $this->repository->encontrar($marca->id);

            if (!$marcaModel) {
                abort(404);
            }

            $this->service->eliminar($marcaModel);

            return redirect()
                ->route('inventario.marcas.index')
                ->with('success', 'Marca eliminada exitosamente.');
        } catch (MarcaException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(Parametro $marca): View
    {
        $marca = $this->repository->encontrarConRelaciones($marca->id);

        if (!$marca) {
            abort(404);
        }

        return view('inventario.marcas.show', [
            'title' => 'Detalle de la marca',
            'icon' => 'fas fa-eye',
            'marca' => $marca
        ]);
    }

}
