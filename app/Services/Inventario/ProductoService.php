<?php

declare(strict_types=1);

namespace App\Services\Inventario;

use App\Inventario\Interfaces\Repositories\Producto\ProductoRepositoryInterface;
use App\Services\Inventario\Interfaces\ImageServiceInterface;
use App\Services\Inventario\Interfaces\BarcodeServiceInterface;
use App\Services\Inventario\Interfaces\StockValidatorServiceInterface;
use App\Models\Inventario\Producto;

/**
 * Servicio para gestión de productos de inventario
 * Cumple SOLID: SRP, OCP, DIP
 */
class ProductoService
{
    protected ProductoRepositoryInterface $repository;
    protected ImageServiceInterface $imageService;
    protected BarcodeServiceInterface $barcodeService;
    protected StockValidatorServiceInterface $stockValidator;

    public function __construct(
        ProductoRepositoryInterface $repository,
        ImageServiceInterface $imageService,
        BarcodeServiceInterface $barcodeService,
        StockValidatorServiceInterface $stockValidator
    ) {
        $this->repository = $repository;
        $this->imageService = $imageService;
        $this->barcodeService = $barcodeService;
        $this->stockValidator = $stockValidator;
    }

    /**
     * Crea un nuevo producto
     *
     * @param array $datos
     * @param int $userId
     * @return Producto
     */
    public function crear(array $datos, int $userId): Producto
    {
        $datos['codigo_barras'] = $this->barcodeService->resolverCodigoBarras($datos['codigo_barras'] ?? null);
        $datos['imagen'] = $this->imageService->procesarImagen($datos['imagen'] ?? null);
        $datos['user_create_id'] = $userId;
        $datos['user_update_id'] = $userId;

        $producto = $this->repository->crear($datos);

        return $producto;
    }

    /**
     * Actualiza un producto existente
     *
     * @param Producto $producto
     * @param array $datos
     * @param int $userId
     * @return Producto
     */
    public function actualizar(Producto $producto, array $datos, int $userId): Producto
    {
        $cantidadAnterior = $producto->cantidad;

        if (isset($datos['imagen']) && $datos['imagen'] instanceof \Illuminate\Http\UploadedFile) {
            $datos['imagen'] = $this->imageService->procesarImagenParaActualizacion(
                $datos['imagen'],
                $producto
            );
        } elseif (!isset($datos['imagen'])) {
            // Mantener imagen actual si no se envía nueva
            unset($datos['imagen']);
        }

        if (isset($datos['codigo_barras'])) {
            $codigoNormalizado = $this->barcodeService->normalizarCodigoBarras($datos['codigo_barras']);
            if ($codigoNormalizado === null) {
                // Si no se puede normalizar, generar uno nuevo
                $datos['codigo_barras'] = $this->barcodeService->generarSiguienteCodigoBarras();
            } else {
                $datos['codigo_barras'] = $codigoNormalizado;
            }
        }

        $datos['user_update_id'] = $userId;

        $this->repository->actualizar($producto, $datos);
        $producto->refresh();

        // Delegado a StockValidatorService (SRP)
        $this->stockValidator->verificarYNotificarCambioStock($producto, $cantidadAnterior);

        return $producto;
    }

    /**
     * Elimina un producto
     *
     * @param Producto $producto
     * @return bool
     */
    public function eliminar(Producto $producto): bool
    {
        $this->imageService->eliminarImagenSiExiste($producto);
        $resultado = $this->repository->eliminar($producto);

        return $resultado;
    }
}
