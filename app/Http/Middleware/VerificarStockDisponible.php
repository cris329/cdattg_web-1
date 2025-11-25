<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Inventario\Producto;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar que hay stock disponible antes de realizar operaciones
 * 
 * Se usa en rutas que requieren verificar stock antes de:
 * - Agregar productos al carrito
 * - Crear órdenes
 * - Procesar préstamos/salidas
 */
class VerificarStockDisponible
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener productos del request
        $productos = $this->obtenerProductosDelRequest($request);

        if (empty($productos)) {
            return $this->respuestaError('No se proporcionaron productos para verificar');
        }

        // Verificar stock para cada producto
        foreach ($productos as $productoData) {
            $productoId = $productoData['producto_id'] ?? $productoData['id'] ?? null;
            $cantidadSolicitada = (int) ($productoData['cantidad'] ?? 1);

            if (!$productoId) {
                return $this->respuestaError('ID de producto no válido');
            }

            $producto = Producto::find($productoId);

            if (!$producto) {
                return $this->respuestaError("El producto con ID {$productoId} no existe");
            }

            // Verificar stock disponible
            if ($producto->cantidad < $cantidadSolicitada) {
                return $this->respuestaError(
                    "Stock insuficiente para el producto '{$producto->producto}'. " .
                    "Disponible: {$producto->cantidad}, Solicitado: {$cantidadSolicitada}"
                );
            }

            // Verificar si el producto está disponible
            if ($producto->estado && $producto->estado->parametro) {
                $estadoNombre = $producto->estado->parametro->name;
                if ($estadoNombre !== 'DISPONIBLE') {
                    return $this->respuestaError(
                        "El producto '{$producto->producto}' no está disponible. Estado: {$estadoNombre}"
                    );
                }
            }
        }

        return $next($request);
    }

    /**
     * Obtiene los productos del request según el formato esperado
     */
    protected function obtenerProductosDelRequest(Request $request): array
    {
        // Formato 1: Array directo de productos
        if ($request->has('productos') && is_array($request->productos)) {
            return $request->productos;
        }

        // Formato 2: Un solo producto en el request
        if ($request->has('producto_id') || $request->has('id')) {
            return [[
                'producto_id' => $request->producto_id ?? $request->id,
                'cantidad' => $request->cantidad ?? 1,
            ]];
        }

        // Formato 3: En el body JSON
        $jsonData = $request->json()->all();
        if (isset($jsonData['productos']) && is_array($jsonData['productos'])) {
            return $jsonData['productos'];
        }

        if (isset($jsonData['producto_id']) || isset($jsonData['id'])) {
            return [[
                'producto_id' => $jsonData['producto_id'] ?? $jsonData['id'],
                'cantidad' => $jsonData['cantidad'] ?? 1,
            ]];
        }

        return [];
    }

    /**
     * Retorna una respuesta de error
     */
    protected function respuestaError(string $mensaje): Response
    {
        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $mensaje,
            ], 422);
        }

        return back()
            ->withInput()
            ->withErrors(['stock' => $mensaje]);
    }
}

