<?php

declare(strict_types=1);

namespace App\Services\Inventario;

use App\Repositories\Interfaces\Inventario\ProveedorRepositoryInterface;
use App\Models\Inventario\Proveedor;
use App\Exceptions\ProveedorException;

class ProveedorService
{
    protected ProveedorRepositoryInterface $repository;

    public function __construct(ProveedorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Crea un nuevo proveedor
     *
     * @param array $datos
     * @param int $userId
     * @return Proveedor
     */
    public function crear(array $datos, int $userId): Proveedor
    {
        $datos['user_create_id'] = $userId;
        $datos['user_update_id'] = $userId;

        return $this->repository->crear($datos);
    }

    /**
     * Actualiza un proveedor existente
     *
     * @param Proveedor $proveedor
     * @param array $datos
     * @param int $userId
     * @return bool
     */
    public function actualizar(Proveedor $proveedor, array $datos, int $userId): bool
    {
        $datos['user_update_id'] = $userId;
        return $this->repository->actualizar($proveedor->id, $datos);
    }

    /**
     * Elimina un proveedor si no está en uso
     *
     * @param Proveedor $proveedor
     * @return bool
     * @throws ProveedorException
     */
    public function eliminar(Proveedor $proveedor): bool
    {
        if ($this->repository->tieneContratos($proveedor->id)) {
            throw new ProveedorException('No se puede eliminar el proveedor porque tiene contratos asociados.');
        }

        if ($this->repository->tieneProductos($proveedor->id)) {
            throw new ProveedorException('No se puede eliminar el proveedor porque tiene productos asociados.');
        }

        return $this->repository->eliminar($proveedor->id);
    }
}

