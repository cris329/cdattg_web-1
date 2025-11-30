<?php

declare(strict_types=1);

namespace App\Inventario\Services\ContratoConvenio;

use App\Inventario\Interfaces\Repositories\ContratoConvenio\ContratoConvenioRepositoryInterface;
use App\Models\Inventario\ContratoConvenio;
use App\Exceptions\ContratoConvenioException;

class ContratoConvenioService
{
    protected ContratoConvenioRepositoryInterface $repository;

    public function __construct(ContratoConvenioRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Crea un nuevo contrato/convenio
     *
     * @param array $datos
     * @param int $userId
     * @return ContratoConvenio
     */
    public function crear(array $datos, int $userId): ContratoConvenio
    {
        $datos['user_create_id'] = $userId;
        $datos['user_update_id'] = $userId;

        return $this->repository->crear($datos);
    }

    /**
     * Actualiza un contrato/convenio existente
     *
     * @param ContratoConvenio $contrato
     * @param array $datos
     * @param int $userId
     * @return bool
     */
    public function actualizar(ContratoConvenio $contrato, array $datos, int $userId): bool
    {
        $datos['user_update_id'] = $userId;
        return $this->repository->actualizar($contrato->id, $datos);
    }

    /**
     * Elimina un contrato/convenio si no está en uso
     *
     * @param ContratoConvenio $contrato
     * @return bool
     * @throws ContratoConvenioException
     */
    public function eliminar(ContratoConvenio $contrato): bool
    {
        if ($this->repository->tieneProductos($contrato->id)) {
            throw new ContratoConvenioException('No se puede eliminar el Contrato/Convenio porque está en uso.');
        }

        return $this->repository->eliminar($contrato->id);
    }
}

