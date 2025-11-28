<?php

declare(strict_types=1);

namespace App\Services\Inventario\Interfaces;

interface BarcodeServiceInterface
{
    public function resolverCodigoBarras(?string $codigo): string;
    public function generarSiguienteCodigoBarras(): string;
    public function normalizarCodigoBarras(?string $codigo): ?string;
}


