<?php

declare(strict_types=1);

namespace App\Inventario\Services\Proveedor;

use App\Inventario\Interfaces\Repositories\Proveedor\ProveedorRepositoryInterface;
use App\Models\Inventario\Proveedor;
use App\Models\Inventario\ProveedorContacto;
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

        // Extraer contacto del array de datos (solo el primero si viene como array)
        $contactoData = $datos['contactos'][0] ?? $datos['contactos'] ?? null;
        unset($datos['contactos']);

        $proveedor = $this->repository->crear($datos);

        // Crear contacto asociado
        if ($contactoData && !empty(array_filter($contactoData))) {
            $contactoData['proveedor_id'] = $proveedor->id;
            $contactoData['user_create_id'] = $userId;
            $contactoData['user_update_id'] = $userId;
            ProveedorContacto::create($contactoData);
        }

        return $proveedor;
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

        $contactoData = null;
        if (isset($datos['contactos'])) {
            $contactoData = is_array($datos['contactos']) && isset($datos['contactos'][0]) 
                ? $datos['contactos'][0] 
                : $datos['contactos'];
            unset($datos['contactos']);
        }

        $resultado = $this->repository->actualizar($proveedor->id, $datos);

        // Actualizar contacto si se proporcionó (uno a uno)
        if ($contactoData !== null) {
            $contactoExistente = ProveedorContacto::where('proveedor_id', $proveedor->id)->first();
            
            if (!empty(array_filter($contactoData))) {
                // Si hay datos de contacto, crear o actualizar
                if ($contactoExistente) {
                    $contactoData['user_update_id'] = $userId;
                    $contactoExistente->update($contactoData);
                } else {
                    $contactoData['proveedor_id'] = $proveedor->id;
                    $contactoData['user_create_id'] = $userId;
                    $contactoData['user_update_id'] = $userId;
                    ProveedorContacto::create($contactoData);
                }
            } else {
                // Si no hay datos, eliminar el contacto existente
                if ($contactoExistente) {
                    $contactoExistente->delete();
                }
            }
        }

        return $resultado;
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

