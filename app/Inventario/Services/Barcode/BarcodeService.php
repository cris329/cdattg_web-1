<?php

declare(strict_types=1);

namespace App\Inventario\Services\Barcode;

use App\Inventario\Interfaces\Services\BarcodeServiceInterface;
use App\Inventario\Interfaces\Repositories\Producto\ProductoRepositoryInterface;

class BarcodeService implements BarcodeServiceInterface
{
    protected ProductoRepositoryInterface $productoRepository;

    public function __construct(ProductoRepositoryInterface $productoRepository)
    {
        $this->productoRepository = $productoRepository;
    }

    /**
     * Obtiene la longitud del código de barras desde configuración
     *
     * @return int
     */
    private function getBarcodeLength(): int
    {
        return config('inventario.codigo_barras.longitud_auto', 11);
    }

    public function resolverCodigoBarras(?string $codigo): string
    {
        $digits = preg_replace('/\D/', '', (string) $codigo);

        if ($digits !== '' && strlen($digits) <= 13) {
            return $digits;
        }

        return $this->generarSiguienteCodigoBarras();
    }

    public function generarSiguienteCodigoBarras(): string
    {
        $max = $this->productoRepository->obtenerMaxCodigoBarras();
        $barcodeLength = $this->getBarcodeLength();

        $onlyDigits = preg_replace('/\D/', '', (string) $max);
        $num = $onlyDigits === '' ? 0 : (int) $onlyDigits;
        $next = $num + 1;
        $code = str_pad((string) $next, $barcodeLength, '0', STR_PAD_LEFT);

        for ($i = 0; $i < 3; $i++) {
            if (!$this->productoRepository->existeCodigoBarras($code)) {
                return $code;
            }

            $code = str_pad(
                (string) ($next + $i + 1),
                $barcodeLength,
                '0',
                STR_PAD_LEFT
            );
        }

        return $code;
    }

    public function normalizarCodigoBarras(?string $codigo): ?string
    {
        if ($codigo === null || $codigo === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $codigo);
        if ($digits === '' || strlen($digits) > 13) {
            return null;
        }

        return $digits;
    }
}

