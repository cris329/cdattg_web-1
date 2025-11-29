<?php

declare(strict_types=1);

namespace App\Inventario\Services\Marca;

use App\Inventario\Interfaces\Repositories\Marca\MarcaRepositoryInterface;
use App\Models\Inventario\Marca;
use App\Exceptions\MarcaException;
use Illuminate\Database\QueryException;

class MarcaService
{
    protected MarcaRepositoryInterface $repository;

    public function __construct(MarcaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Crea una nueva marca
     *
     * @param array $datos
     * @param int $userId
     * @return Marca
     * @throws MarcaException
     */
    public function crear(array $datos, int $userId): Marca
    {
        try {
            $datos['status'] = 1;
            $datos['user_create_id'] = $userId;
            $datos['user_edit_id'] = $userId;

            $marca = new Marca($datos);
            $marca->save();

            // Asociar al tema "MARCAS"
            $marca->asociarATemaMarcas();

            return $marca;
        } catch (QueryException $e) {
            throw new MarcaException('Error al crear la marca: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza una marca existente
     *
     * @param Marca $marca
     * @param array $datos
     * @param int $userId
     * @return bool
     */
    public function actualizar(Marca $marca, array $datos, int $userId): bool
    {
        if (isset($datos['name'])) {
            $datos['name'] = strtoupper($datos['name']);
        }
        $datos['user_edit_id'] = $userId;

        return $this->repository->actualizar($marca->id, $datos);
    }

    /**
     * Elimina una marca si no está en uso
     *
     * @param Marca $marca
     * @return bool
     * @throws MarcaException
     */
    public function eliminar(Marca $marca): bool
    {
        if ($this->repository->tieneProductos($marca->id)) {
            throw new MarcaException('No se puede eliminar la marca porque está en uso.');
        }

        $temaMarcas = $this->repository->obtenerTemaMarcas();

        if (!$temaMarcas) {
            throw new MarcaException('No existe el tema "MARCAS" en la base de datos.');
        }

        return $this->repository->eliminar($marca, $temaMarcas->id);
    }
}

