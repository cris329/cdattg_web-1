<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Inventario\Interfaces\Repositories\Producto\ProductoRepositoryInterface;
use App\Inventario\Services\Carrito\CarritoService;
use App\Exceptions\CarritoException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\Inventario\CarritoRequest;

class CarritoController extends Controller
{
    protected CarritoService $service;
    protected ProductoRepositoryInterface $productoRepository;

    public function __construct(
        CarritoService $service,
        ProductoRepositoryInterface $productoRepository
    ) {
        // Middlewares de permisos de carrito
        $this->middleware('can:VER CARRITO')->only(['index']);
        $this->middleware('can:AGREGAR CARRITO')->only(['agregar', 'store']);
        $this->middleware('can:ACTUALIZAR CARRITO')->only(['actualizar', 'update']);
        $this->middleware('can:ELIMINAR CARRITO')->only(['eliminar', 'destroy']);
        $this->middleware('can:VACIAR CARRITO')->only(['vaciar']);

        $this->service = $service;
        $this->productoRepository = $productoRepository;
    }

    // Vista del carrito
    public function index() : View
    {
        return view('inventario.carrito.carrito');
    }

    // Agregar productos al carrito (crear orden)
    public function agregar(CarritoRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $erroresStock = $this->service->verificarDisponibilidad($validated['items']);

            if (!empty($erroresStock)) {
                return $this->respuestaErrorStock($erroresStock);
            }

            return $this->respuestaExitoAgregar();

        } catch (\Exception $e) {
            return $this->manejarExcepcionAgregar($e);
        }
    }

    // Actualizar cantidad de un producto en el carrito
    public function actualizar(CarritoRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $resultado = $this->service->validarItem($id, (int)$validated['cantidad']);

            $codigoHttp = $resultado['success'] ? 200 : 400;
            return response()->json($resultado, $codigoHttp);

        } catch (CarritoException $e) {
            return $this->respuestaErrorCarrito($e);
        } catch (\Exception $e) {
            return $this->respuestaErrorGenerico('Error al actualizar: ' . $e->getMessage());
        }
    }

    // Eliminar producto del carrito
    public function eliminar(int $id): JsonResponse
    {
        try {
            // Esta es una operación del lado del cliente (localStorage)
            // Solo validamos que el producto existe
            $producto = $this->productoRepository->encontrar($id);

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado del carrito'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }


    public function vaciar(): JsonResponse
    {
        // Esta es una operación del lado del cliente (localStorage)
        return response()->json([
            'success' => true,
            'message' => 'Carrito vaciado correctamente'
        ]);
    }

    // Obtener contenido del carrito
    public function contenido(Request $request): JsonResponse
    {
        try {
            $items = $request->input('items', []);
            $productos = $this->service->obtenerProductosParaCarrito($items);

            return response()->json([
                'success' => true,
                'productos' => $productos->values()->all()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener contenido: ' . $e->getMessage()
            ], 500);
        }
    }

    private function respuestaErrorStock(array $erroresStock): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Stock insuficiente para algunos productos',
            'errores' => $erroresStock
        ], 400);
    }

    private function respuestaExitoAgregar(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Solicitud procesada correctamente',
            'orden_id' => null
        ]);
    }

    private function respuestaErrorCarrito(CarritoException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 404);
    }

    private function respuestaErrorGenerico(string $mensaje): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $mensaje
        ], 500);
    }

    private function manejarExcepcionAgregar(\Exception $e): JsonResponse
    {
        if ($e instanceof CarritoException) {
            return $this->respuestaErrorCarrito($e);
        }

        return $this->respuestaErrorGenerico('Error al procesar la solicitud: ' . $e->getMessage());
    }
}

