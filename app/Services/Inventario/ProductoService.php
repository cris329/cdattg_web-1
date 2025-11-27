<?php

declare(strict_types=1);

namespace App\Services\Inventario;

use App\Repositories\Interfaces\Inventario\ProductoRepositoryInterface;
use App\Models\Inventario\Producto;
use App\Models\ParametroTema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Notifications\StockBajoNotification;

class ProductoService
{
    private const DEFAULT_PRODUCT_IMAGE = 'img/inventario/producto-default.png';
    private const BARCODE_LENGTH = 11;

    protected ProductoRepositoryInterface $repository;

    public function __construct(ProductoRepositoryInterface $repository)
    {
        $this->repository = $repository;
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
        $datos['codigo_barras'] = $this->resolverCodigoBarras($datos['codigo_barras'] ?? null);
        $datos['imagen'] = $this->procesarImagen($datos['imagen'] ?? null);
        $datos['user_create_id'] = $userId;
        $datos['user_update_id'] = $userId;

        $producto = Producto::create($datos);

        $this->repository->invalidarCache();

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
            $datos['imagen'] = $this->procesarImagenParaActualizacion(
                $datos['imagen'],
                $producto
            );
        } elseif (!isset($datos['imagen'])) {
            // Mantener imagen actual si no se envía nueva
            unset($datos['imagen']);
        }

        if (isset($datos['codigo_barras'])) {
            $codigoNormalizado = $this->normalizarCodigoBarras($datos['codigo_barras']);
            if ($codigoNormalizado === null) {
                // Si no se puede normalizar, generar uno nuevo
                $datos['codigo_barras'] = $this->generarSiguienteCodigoBarras();
            } else {
                $datos['codigo_barras'] = $codigoNormalizado;
            }
        }

        $datos['user_update_id'] = $userId;

        $producto->update($datos);

        $this->verificarYNotificarStockBajo($producto, $cantidadAnterior);
        $this->repository->invalidarCache();

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
        $this->eliminarImagenSiExiste($producto);
        $resultado = $producto->delete();
        $this->repository->invalidarCache();

        return $resultado;
    }

    /**
     * Resuelve el código de barras para creación
     *
     * @param string|null $codigo
     * @return string
     */
    public function resolverCodigoBarras(?string $codigo): string
    {
        $digits = preg_replace('/\D/', '', (string) $codigo);
        
        if (strlen($digits) === self::BARCODE_LENGTH) {
            return $digits;
        }

        return $this->generarSiguienteCodigoBarras();
    }

    /**
     * Genera el siguiente código de barras disponible
     *
     * @return string
     */
    public function generarSiguienteCodigoBarras(): string
    {
        return DB::transaction(function () {
            $max = DB::table('productos')
                ->whereNotNull('codigo_barras')
                ->max('codigo_barras');

            $onlyDigits = preg_replace('/\D/', '', (string) $max);
            $num = $onlyDigits === '' ? 0 : (int) $onlyDigits;
            $next = $num + 1;
            $code = str_pad((string) $next, self::BARCODE_LENGTH, '0', STR_PAD_LEFT);

            for ($i = 0; $i < 3; $i++) {
                $exists = DB::table('productos')
                    ->where('codigo_barras', $code)
                    ->exists();

                if (!$exists) {
                    return $code;
                }

                $code = str_pad(
                    (string) ($next + $i + 1),
                    self::BARCODE_LENGTH,
                    '0',
                    STR_PAD_LEFT
                );
            }

            return $code;
        }, 3);
    }

    /**
     * Normaliza código de barras para actualización
     *
     * @param string|null $codigo
     * @return string|null
     */
    public function normalizarCodigoBarras(?string $codigo): ?string
    {
        if (empty($codigo)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $codigo);
        
        return strlen($digits) === self::BARCODE_LENGTH ? $digits : null;
    }

    /**
     * Procesa imagen para creación
     *
     * @param UploadedFile|null $imagen
     * @return string
     */
    public function procesarImagen(?UploadedFile $imagen): string
    {
        if (!$imagen || !$imagen->isValid()) {
            return self::DEFAULT_PRODUCT_IMAGE;
        }

        $nombreArchivo = time() . '.' . $imagen->extension();
        $imagen->move(public_path('img/inventario'), $nombreArchivo);

        return 'img/inventario/' . $nombreArchivo;
    }

    /**
     * Procesa imagen para actualización
     *
     * @param UploadedFile|null $imagen
     * @param Producto $producto
     * @return string
     */
    public function procesarImagenParaActualizacion(
        ?UploadedFile $imagen,
        Producto $producto
    ): string {
        if (!$imagen || !$imagen->isValid()) {
            return $producto->imagen ?? self::DEFAULT_PRODUCT_IMAGE;
        }

        $this->eliminarImagenSiExiste($producto);

        return $this->procesarImagen($imagen);
    }

    /**
     * Elimina imagen si existe y no es la por defecto
     *
     * @param Producto $producto
     * @return void
     */
    public function eliminarImagenSiExiste(Producto $producto): void
    {
        if ($producto->imagen &&
            $producto->imagen !== self::DEFAULT_PRODUCT_IMAGE &&
            file_exists(public_path($producto->imagen))) {
            unlink(public_path($producto->imagen));
        }
    }

    /**
     * Verifica y notifica si el stock está bajo
     *
     * @param Producto $producto
     * @param int $cantidadAnterior
     * @return void
     */
    public function verificarYNotificarStockBajo(Producto $producto, int $cantidadAnterior): void
    {
        if ($cantidadAnterior == $producto->cantidad || $producto->cantidad > 10) {
            return;
        }

        $superadmins = \App\Models\User::role('SUPER ADMINISTRADOR')->get();

        if ($superadmins->isEmpty()) {
            return;
        }

        foreach ($superadmins as $admin) {
            $admin->notify(new StockBajoNotification($producto, $producto->cantidad, 10));
        }
    }

    /**
     * Obtiene opciones para formularios (tipos, unidades, estados, etc.)
     *
     * @param string $temaEstados
     * @return array
     */
    public function obtenerOpcionesFormulario(string $temaEstados = 'ESTADOS DE PRODUCTO'): array
    {
        return [
            'tiposProductos' => ParametroTema::with(['parametro', 'tema'])
                ->whereHas('tema', fn($q) => $q->where('name', 'TIPOS DE PRODUCTO'))
                ->where('status', 1)
                ->get(),
            'unidadesMedida' => ParametroTema::with(['parametro', 'tema'])
                ->whereHas('tema', fn($q) => $q->where('name', 'UNIDADES DE MEDIDA'))
                ->where('status', 1)
                ->get(),
            'estados' => ParametroTema::with(['parametro', 'tema'])
                ->whereHas('tema', fn($q) => $q->where('name', $temaEstados))
                ->where('status', 1)
                ->get(),
            'categorias' => ParametroTema::with(['parametro', 'tema'])
                ->whereHas('tema', fn($q) => $q->where('name', 'CATEGORIAS'))
                ->where('status', 1)
                ->get(),
            'marcas' => ParametroTema::with(['parametro', 'tema'])
                ->whereHas('tema', fn($q) => $q->where('name', 'MARCAS'))
                ->where('status', 1)
                ->get(),
        ];
    }
}

