<?php

declare(strict_types=1);

namespace App\Inventario\Services\StockValidator;

use App\Models\Inventario\Producto;
use App\Inventario\Interfaces\Services\NotificationServiceInterface;
use App\Inventario\Interfaces\Services\StockValidatorServiceInterface;

/**
 * Servicio para validación de stock y notificaciones
 * Cumple SRP: responsabilidad única de validar stock
 * Cumple OCP: extensible mediante configuración
 */
class StockValidatorService implements StockValidatorServiceInterface
{
    protected NotificationServiceInterface $notificationService;

    public function __construct(NotificationServiceInterface $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Verifica si el stock está bajo el umbral mínimo
     *
     * @param Producto $producto
     * @return bool
     */
    public function estaBajoUmbralMinimo(Producto $producto): bool
    {
        $umbralMinimo = config('inventario.stock.umbral_minimo', 10);
        return $producto->cantidad <= $umbralMinimo;
    }

    /**
     * Verifica si el stock está en nivel crítico
     *
     * @param Producto $producto
     * @return bool
     */
    public function estaNivelCritico(Producto $producto): bool
    {
        $umbralCritico = config('inventario.stock.umbral_critico', 5);
        return $producto->cantidad <= $umbralCritico;
    }

    /**
     * Verifica si hay stock suficiente
     *
     * @param Producto $producto
     * @param int $cantidadRequerida
     * @return bool
     */
    public function hayStockSuficiente(Producto $producto, int $cantidadRequerida): bool
    {
        return $producto->cantidad >= $cantidadRequerida;
    }

    /**
     * Verifica y notifica si el stock cambió a bajo
     *
     * @param Producto $producto
     * @param int $cantidadAnterior
     * @return void
     */
    public function verificarYNotificarCambioStock(Producto $producto, int $cantidadAnterior): void
    {
        if ($this->debeNotificarCambioStock($producto, $cantidadAnterior)) {
            $umbralMinimo = $this->getUmbralMinimo();
            $this->notificationService->notificarStockBajo(
                $producto,
                $producto->cantidad,
                $umbralMinimo
            );
        }
    }

    private function debeNotificarCambioStock(Producto $producto, int $cantidadAnterior): bool
    {
        if ($cantidadAnterior === $producto->cantidad) {
            return false;
        }

        if (!config('inventario.stock.notificar_stock_bajo', true)) {
            return false;
        }

        $umbralMinimo = $this->getUmbralMinimo();
        if ($cantidadAnterior <= $umbralMinimo) {
            return false;
        }

        return $this->estaBajoUmbralMinimo($producto);
    }

    /**
     * Obtiene el umbral mínimo configurado
     *
     * @return int
     */
    public function getUmbralMinimo(): int
    {
        return config('inventario.stock.umbral_minimo', 10);
    }

    /**
     * Obtiene el umbral crítico configurado
     *
     * @return int
     */
    public function getUmbralCritico(): int
    {
        return config('inventario.stock.umbral_critico', 5);
    }

    /**
     * Calcula el porcentaje de stock disponible
     *
     * @param Producto $producto
     * @param int $stockMaximo
     * @return float
     */
    public function calcularPorcentajeStock(Producto $producto, int $stockMaximo): float
    {
        if ($stockMaximo <= 0) {
            return 0.0;
        }

        return ($producto->cantidad / $stockMaximo) * 100;
    }

    /**
     * Obtiene nivel de stock (crítico, bajo, normal, alto)
     *
     * @param Producto $producto
     * @return string
     */
    public function obtenerNivelStock(Producto $producto): string
    {
        if ($this->estaNivelCritico($producto)) {
            return 'critico';
        }

        if ($this->estaBajoUmbralMinimo($producto)) {
            return 'bajo';
        }

        $umbralMinimo = $this->getUmbralMinimo();
        if ($producto->cantidad <= ($umbralMinimo * 2)) {
            return 'normal';
        }

        return 'alto';
    }

    /**
     * Valida que haya stock suficiente, lanza excepción si no
     *
     * @param Producto $producto
     * @param int $cantidadRequerida
     * @return void
     * @throws \App\Exceptions\OrdenException
     */
    public function validarStockSuficiente(Producto $producto, int $cantidadRequerida): void
    {
        if (!$this->hayStockSuficiente($producto, $cantidadRequerida)) {
            throw new \App\Exceptions\OrdenException(
                "Stock insuficiente para '{$producto->producto}'. " .
                "Disponible: {$producto->cantidad}, Solicitado: {$cantidadRequerida}"
            );
        }
    }
}

