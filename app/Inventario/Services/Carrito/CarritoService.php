<?php

declare(strict_types=1);

namespace App\Inventario\Services\Carrito;

use App\Inventario\Interfaces\Repositories\Producto\ProductoRepositoryInterface;
use App\Exceptions\CarritoException;
use Illuminate\Support\Collection;

class CarritoService
{
    protected ProductoRepositoryInterface $productoRepository;

    public function __construct(ProductoRepositoryInterface $productoRepository)
    {
        $this->productoRepository = $productoRepository;
    }

    /**
     * Verifica la disponibilidad de productos en el carrito
     *
     * @param array $items Array de items con 'producto_id' y 'cantidad'
     * @return array Array con errores de stock si los hay
     * @throws CarritoException
     */
    public function verificarDisponibilidad(array $items): array
    {
        $erroresStock = [];

        foreach ($items as $item) {
            $productoId = $item['producto_id'] ?? $item['id'] ?? null;
            $cantidad = (int)($item['cantidad'] ?? $item['quantity'] ?? 0);

            if (!$productoId || $cantidad <= 0) {
                continue;
            }

            $producto = $this->productoRepository->encontrar($productoId);

            if (!$producto) {
                throw new CarritoException("Producto con ID {$productoId} no encontrado.");
            }

            if ($producto->cantidad < $cantidad) {
                $erroresStock[] = [
                    'producto' => $producto->name,
                    'solicitado' => $cantidad,
                    'disponible' => $producto->cantidad
                ];
            }
        }

        return $erroresStock;
    }

    /**
     * Valida un item individual del carrito
     *
     * @param int $productoId
     * @param int $cantidad
     * @return array
     * @throws CarritoException
     */
    public function validarItem(int $productoId, int $cantidad): array
    {
        $producto = $this->productoRepository->encontrar($productoId);

        if (!$producto) {
            throw new CarritoException('Producto no encontrado');
        }

        if ($producto->cantidad < $cantidad) {
            return [
                'success' => false,
                'message' => 'Stock insuficiente',
                'stock_disponible' => $producto->cantidad
            ];
        }

        return [
            'success' => true,
            'message' => 'Cantidad válida',
            'producto' => [
                'id' => $producto->id,
                'nombre' => $producto->name,
                'stock' => $producto->cantidad
            ]
        ];
    }

    /**
     * Obtiene información de productos para el carrito
     *
     * @param array $items Array de items con 'id'
     * @return Collection
     */
    public function obtenerProductosParaCarrito(array $items): Collection
    {
        $productos = collect();

        foreach ($items as $item) {
            $productoId = $item['id'] ?? $item['producto_id'] ?? null;

            if (!$productoId) {
                continue;
            }

            $producto = $this->productoRepository->encontrarConRelaciones($productoId);

            if ($producto) {
                $productos->push([
                    'id' => $producto->id,
                    'nombre' => $producto->name,
                    'codigo' => $producto->codigo_barras,
                    'imagen' => $producto->imagen,
                    'stock' => $producto->cantidad,
                    'categoria' => $producto->categoria->name ?? 'Sin categoría',
                    'marca' => $producto->marca->name ?? 'Sin marca',
                    'descripcion' => $producto->descripcion
                ]);
            }
        }

        return $productos;
    }
}

