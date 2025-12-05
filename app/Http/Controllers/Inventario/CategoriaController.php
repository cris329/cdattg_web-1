<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventario;

use App\Inventario\Interfaces\Repositories\Categoria\CategoriaRepositoryInterface;
use App\Inventario\Services\Categoria\CategoriaService;
use App\Models\Parametro;
use App\Exceptions\CategoriaException;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Inventario\MarcaCategoriaRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class CategoriaController extends Controller
{
    protected CategoriaRepositoryInterface $repository;
    protected CategoriaService $service;

    public function __construct(
        CategoriaRepositoryInterface $repository,
        CategoriaService $service
    ) {

        $this->middleware('can:VER CATEGORIA')->only('index', 'show');
        $this->middleware('can:CREAR CATEGORIA')->only('create', 'store');
        $this->middleware('can:EDITAR CATEGORIA')->only('edit', 'update');
        $this->middleware('can:ELIMINAR CATEGORIA')->only('destroy');

        $this->repository = $repository;
        $this->service = $service;
    }

    public function index(Request $request): View|RedirectResponse
    {
        $temaCategorias = $this->repository->obtenerTemaCategorias();

        if (!$temaCategorias) {
            return back()->with('error', 'No existe el tema "CATEGORIAS" en la base de datos.');
        }

        $filtros = [
            'search' => $request->input('search'),
            'per_page' => 10
        ];

        $categorias = $this->repository->obtenerConFiltros($filtros);
        $categorias->appends($request->only('search'));

        return view('inventario.categorias.index', compact('categorias'));
    }

    public function create() : View
    {
        return view('inventario.categorias.create');
    }


    public function store(MarcaCategoriaRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $this->service->crear($validated, Auth::id());

            return redirect()
                ->route('inventario.categorias.index')
                ->with('success', 'Categoria creada exitosamente.');
        } catch (CategoriaException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit(Parametro $categoria) : View
    {
        return view('inventario.categorias.edit', [
            'title' => 'Editar categoria',
            'icon' => 'fas fa-tag',
            'action' => route('inventario.categorias.update', $categoria->id),
            'method' => 'PUT',
            'submitText' => 'Actualizar categoria',
            'cancelRoute' => route('inventario.categorias.index'),
            'categoria' => $categoria
        ]);
    }


    public function update(MarcaCategoriaRequest $request, Parametro $categoria): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $categoriaModel = $this->repository->encontrar($categoria->id);

            if (!$categoriaModel) {
                abort(404);
            }

            $this->service->actualizar($categoriaModel, $validated, Auth::id());

            return redirect()
                ->route('inventario.categorias.index')
                ->with('success', 'categoria actualizada exitosamente.');
        } catch (CategoriaException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Parametro $categoria): RedirectResponse
    {
        try {
            $categoriaModel = $this->repository->encontrar($categoria->id);

            if (!$categoriaModel) {
                abort(404);
            }

            $this->service->eliminar($categoriaModel);

            return redirect()
                ->route('inventario.categorias.index')
                ->with('success', 'categoria eliminada exitosamente.');
        } catch (CategoriaException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(Parametro $categoria): View
    {
        $categoria = $this->repository->encontrarConRelaciones($categoria->id);

        if (!$categoria) {
            abort(404);
        }

        return view('inventario.categorias.show', [
            'title' => 'Detalle de la categoria',
            'icon' => 'fas fa-eye',
            'categoria' => $categoria
        ]);
    }

}
