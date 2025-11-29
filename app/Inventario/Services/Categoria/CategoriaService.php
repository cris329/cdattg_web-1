<?php

declare(strict_types=1);

namespace App\Inventario\Services\Categoria;

use App\Inventario\Interfaces\Repositories\Categoria\CategoriaRepositoryInterface;
use App\Models\Inventario\Categoria;
use App\Exceptions\CategoriaException;
use Illuminate\Database\QueryException;

class CategoriaService
{
    protected CategoriaRepositoryInterface $repository;

    public function __construct(CategoriaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Crea una nueva categoría
     *
     * @param array $datos
     * @param int $userId
     * @return Categoria
     * @throws CategoriaException
     */
    public function crear(array $datos, int $userId): Categoria
    {
        try {
            $datos['status'] = 1;
            $datos['user_create_id'] = $userId;
            $datos['user_edit_id'] = $userId;

            $categoria = new Categoria($datos);
            $categoria->save();

            // Asociar al tema "CATEGORIAS"
            $categoria->asociarATemaCategorias();

            return $categoria;
        } catch (QueryException $e) {
            throw new CategoriaException('Error al crear la categoria: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza una categoría existente
     *
     * @param Categoria $categoria
     * @param array $datos
     * @param int $userId
     * @return bool
     */
    public function actualizar(Categoria $categoria, array $datos, int $userId): bool
    {
        if (isset($datos['name'])) {
            $datos['name'] = strtoupper($datos['name']);
        }
        $datos['user_edit_id'] = $userId;

        return $this->repository->actualizar($categoria->id, $datos);
    }

    /**
     * Elimina una categoría si no está en uso
     *
     * @param Categoria $categoria
     * @return bool
     * @throws CategoriaException
     */
    public function eliminar(Categoria $categoria): bool
    {
        if ($this->repository->tieneProductos($categoria->id)) {
            throw new CategoriaException('No se puede eliminar la categoria porque está en uso.');
        }

        $temaCategorias = $this->repository->obtenerTemaCategorias();

        if (!$temaCategorias) {
            throw new CategoriaException('No existe el tema "CATEGORIAS" en la base de datos.');
        }

        return $this->repository->eliminar($categoria, $temaCategorias->id);
    }
}

