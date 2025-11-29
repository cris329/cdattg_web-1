<?php

declare(strict_types=1);

namespace App\Inventario\Services\ProductoEnrichment;

use App\Models\Inventario\Producto;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Inventario\Interfaces\Repositories\Marca\MarcaRepositoryInterface;
use App\Inventario\Interfaces\Repositories\Categoria\CategoriaRepositoryInterface;


/**
 * Servicio para enriquecer productos con relaciones
 * Cumple SRP: responsabilidad única de cargar relaciones de productos
 */
class ProductoEnrichmentService
{
    public function __construct(
        protected MarcaRepositoryInterface $marcaRepository,
        protected CategoriaRepositoryInterface $categoriaRepository
    ) {}

    /**
     * Enriquece una colección de productos con sus marcas y categorías
     *
     * @param iterable|LengthAwarePaginator $productos
     * @return void
     */
    public function enriquecerConMarcasYCategorias(iterable|LengthAwarePaginator $productos): void
    {
        $marcaIds = [];
        $categoriaIds = [];

        // Recolectar todos los IDs únicos
        foreach ($productos as $producto) {
            if ($producto->marca_id) {
                $marcaIds[] = $producto->marca_id;
            }
            if ($producto->categoria_id) {
                $categoriaIds[] = $producto->categoria_id;
            }
        }

        // Cargar todos los parámetros usando repositorios
        $marcas = !empty($marcaIds)
            ? $this->marcaRepository->encontrarMultiples(array_unique($marcaIds))
            : collect();

        $categorias = !empty($categoriaIds)
            ? $this->categoriaRepository->encontrarMultiples(array_unique($categoriaIds))
            : collect();

        // Asignar relaciones
        foreach ($productos as $producto) {
            if ($producto->marca_id && isset($marcas[$producto->marca_id])) {
                $producto->setRelation('marca', $marcas[$producto->marca_id]);
            }
            if ($producto->categoria_id && isset($categorias[$producto->categoria_id])) {
                $producto->setRelation('categoria', $categorias[$producto->categoria_id]);
            }
        }
    }

    /**
     * Enriquece un solo producto con sus relaciones
     *
     * @param Producto $producto
     * @return void
     */
    public function enriquecerProducto(Producto $producto): void
    {
        if ($producto->marca_id) {
            $marca = $this->marcaRepository->encontrar($producto->marca_id);
            if ($marca) {
                $producto->setRelation('marca', $marca);
            }
        }

        if ($producto->categoria_id) {
            $categoria = $this->categoriaRepository->encontrar($producto->categoria_id);
            if ($categoria) {
                $producto->setRelation('categoria', $categoria);
            }
        }
    }
}

