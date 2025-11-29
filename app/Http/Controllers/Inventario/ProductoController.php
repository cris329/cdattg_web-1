<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Inventario\Interfaces\Repositories\Producto\ProductoRepositoryInterface;
use App\Inventario\Services\Producto\ProductoService;
use App\Inventario\Interfaces\Services\FormOptionsServiceInterface;
use App\Inventario\Interfaces\Services\StockValidatorServiceInterface;
use App\Inventario\Services\ProductoEnrichment\ProductoEnrichmentService;
use App\Inventario\Services\FormData\FormDataService;
use App\Exceptions\OrdenException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Inventario\ProductoRequest;


class ProductoController extends Controller
{
    private const THEME_PRODUCT_STATES = 'ESTADOS DE PRODUCTO';

    protected ProductoRepositoryInterface $repository;
    protected ProductoService $service;
    protected FormOptionsServiceInterface $formOptionsService;
    protected StockValidatorServiceInterface $stockValidator;
    protected ProductoEnrichmentService $enrichmentService;
    protected FormDataService $formDataService;

    public function __construct(
        ProductoRepositoryInterface $repository,
        ProductoService $service,
        FormOptionsServiceInterface $formOptionsService,
        StockValidatorServiceInterface $stockValidator,
        ProductoEnrichmentService $enrichmentService,
        FormDataService $formDataService
    ) {
        $this->middleware('auth');

        $this->repository = $repository;
        $this->service = $service;
        $this->formOptionsService = $formOptionsService;
        $this->stockValidator = $stockValidator;
        $this->enrichmentService = $enrichmentService;
        $this->formDataService = $formDataService;

        // Middlewares de permisos de inventario
        $this->middleware('can:VER PRODUCTO')->only(['index', 'show']);
        $this->middleware('can:VER CATALOGO PRODUCTO')->only(['catalogo']);
        $this->middleware('can:BUSCAR PRODUCTO')->only(['buscar']);
        $this->middleware('can:CREAR PRODUCTO')->only(['create', 'store']);
        $this->middleware('can:EDITAR PRODUCTO')->only(['edit', 'update']);
        $this->middleware('can:ELIMINAR PRODUCTO')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filtros = [
            'search' => $request->input('search'),
            'per_page' => 10
        ];

        $productos = $this->repository->obtenerConFiltros($filtros);
        $productos->appends($request->only('search'));

        // Enriquecer productos con marcas y categorías
        $this->enrichmentService->enriquecerConMarcasYCategorias($productos);

        $tiposProductos = $this->repository->obtenerTiposProductos();

        return view('inventario.productos.index', compact('productos', 'tiposProductos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $opciones = $this->formOptionsService->obtenerOpcionesProducto(self::THEME_PRODUCT_STATES);
        $datosFormulario = $this->formDataService->obtenerDatosFormulario();

        $filtros = [
            'per_page' => 12
        ];
        $productos = $this->repository->obtenerParaCatalogo($filtros);
        $tiposProductos = $this->repository->obtenerTiposProductos();

        // Enriquecer productos con marcas y categorías
        $this->enrichmentService->enriquecerConMarcasYCategorias($productos);

        return view(
            'inventario.productos.create',
            array_merge($opciones, $datosFormulario, [
                'productos' => $productos,
                'tiposProductos' => $tiposProductos
            ])
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductoRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['imagen'] = $request->hasFile('imagen') ? $request->file('imagen') : null;

        $this->service->crear($validated, Auth::id());

        return redirect()
            ->route('inventario.productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $producto = $this->repository->encontrarConRelaciones((int) $id);

        if (!$producto) {
            abort(404);
        }

        return view('inventario.productos.show', compact('producto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $producto = $this->repository->encontrarConRelaciones((int) $id);

        if (!$producto) {
            abort(404);
        }
        $opciones = $this->formOptionsService->obtenerOpcionesProducto(self::THEME_PRODUCT_STATES);
        $datosFormulario = $this->formDataService->obtenerDatosFormulario();

        return view('inventario.productos.edit', array_merge($opciones, $datosFormulario, [
            'producto' => $producto
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductoRequest $request, string $id): RedirectResponse
    {
        $producto = $this->repository->encontrar((int) $id);

        if (!$producto) {
            abort(404);
        }
        $validated = $request->validated();

        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $request->file('imagen');
        }

        $this->service->actualizar($producto, $validated, Auth::id());

        return redirect()
            ->route('inventario.productos.show', $producto->id)
            ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $producto = $this->repository->encontrar((int) $id);

        if (!$producto) {
            abort(404);
        }
        $this->service->eliminar($producto);

        return redirect()
            ->route('inventario.productos.index')
            ->with('success', 'Producto eliminado correctamente');
    }

    public function buscarPorCodigo(string $codigo): JsonResponse
    {
        $producto = $this->repository->buscarPorCodigoBarras($codigo);

        if ($producto) {
            return response()->json($producto);
        }

        return response()->json(null, 404);
    }

    /**
     * Mostrar catálogo de productos estilo ecommerce
     */
    public function catalogo(Request $request): View
    {
        $estadoAgotado = $this->formOptionsService->obtenerEstadoAgotado(self::THEME_PRODUCT_STATES);

        $filtros = [
            'search' => $request->input('search'),
            'tipo_producto_id' => $request->input('tipo_producto_id'),
            'sort_by' => $request->input('sort_by', 'name'),
            'estado_agotado_id' => $estadoAgotado?->id,
            'per_page' => 12
        ];

        $productos = $this->repository->obtenerParaCatalogo($filtros);
        $productos->appends([
            'search' => $filtros['search'],
            'tipo_producto_id' => $filtros['tipo_producto_id'],
            'sort_by' => $filtros['sort_by']
        ]);

        // Enriquecer productos con marcas y categorías (SRP)
        $this->enrichmentService->enriquecerConMarcasYCategorias($productos);

        $tiposProductos = $this->repository->obtenerTiposProductos();

        return view('inventario.productos.card', compact('productos', 'tiposProductos'));
    }

    /**
     * Buscar productos por término de búsqueda (AJAX)
     */
    public function buscar(Request $request): JsonResponse
    {
        $estadoAgotado = $this->formOptionsService->obtenerEstadoAgotado(self::THEME_PRODUCT_STATES);

        $filtros = [
            'search' => $request->input('search'),
            'tipo_producto_id' => $request->input('tipo_producto_id'),
            'estado_agotado_id' => $estadoAgotado?->id
        ];

        $productos = $this->repository->buscarParaAjax($filtros);

        // Enriquecer productos con marcas y categorías (SRP)
        $this->enrichmentService->enriquecerConMarcasYCategorias($productos);

        foreach ($productos as $producto) {
            $producto->imagen_url = $producto->imagen ? asset($producto->imagen) : null;
        }

        return response()->json([
            'success' => true,
            'productos' => $productos
        ]);
    }


    /**
     * Agregar producto al carrito (AJAX)
     */
    public function agregarAlCarrito(ProductoRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $producto = $this->repository->encontrar((int) $validated['producto_id']);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        try {
            $this->stockValidator->validarStockSuficiente($producto, $validated['cantidad']);
        } catch (OrdenException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuficiente',
                'stock_disponible' => $producto->cantidad
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Producto agregado al carrito',
            'producto' => [
                'id' => $producto->id,
                'nombre' => $producto->producto,
                'stock' => $producto->cantidad
            ]
        ]);
    }

    /**
     * Obtener detalles del producto para modal
     */
    public function detalles(string $id): View
    {
        $producto = $this->repository->encontrarConRelaciones((int) $id);

        if (!$producto) {
            abort(404);
        }

        // Enriquecer producto con marca y categoría usando el servicio
        $this->enrichmentService->enriquecerProducto($producto);

        return view('inventario.productos._detalles-modal', compact('producto'));
    }

    /**
     * Vista imprimible de la etiqueta con código de barras SENA (JS en cliente)
     */
    public function etiqueta(string $id): View
    {
        $producto = $this->repository->encontrar((int) $id);

        if (!$producto) {
            abort(404);
        }
        return view('inventario.productos.etiqueta', compact('producto'));
    }
}

