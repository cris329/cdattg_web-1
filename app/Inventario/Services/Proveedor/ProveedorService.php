<?php

declare(strict_types=1);

namespace App\Inventario\Services\Proveedor;

use App\Inventario\Interfaces\Repositories\Proveedor\ProveedorRepositoryInterface;
use App\Models\Inventario\Proveedor;
use App\Exceptions\ProveedorException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProveedorService
{
    protected ProveedorRepositoryInterface $repository;

    public function __construct(
        ProveedorRepositoryInterface $repository
    ) {
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
        return DB::transaction(function () use ($datos, $userId) {
            $datos['user_create_id'] = $userId;
            $datos['user_update_id'] = $userId;

            $proveedor = $this->repository->crear($datos);

            return $proveedor;
        });
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
        return DB::transaction(function () use ($proveedor, $datos, $userId) {
            $datos['user_update_id'] = $userId;

            $resultado = $this->repository->actualizar($proveedor->id, $datos);

            return $resultado;
        });
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
