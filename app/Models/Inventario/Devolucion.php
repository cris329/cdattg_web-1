<?php

declare(strict_types=1);

namespace App\Models\Inventario;

use App\Exceptions\DevolucionException;
use App\Traits\Seguimiento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Devolucion extends Model
{
    use HasFactory, Seguimiento;

    protected $table = 'devoluciones';

    protected $fillable = [
        'detalle_orden_id',
        'cantidad_devuelta',
        'fecha_devolucion',
        'estado_id',
        'observaciones',
        'cierra_sin_stock',
        'user_create_id',
        'user_update_id'
    ];

    protected $casts = [
        'fecha_devolucion' => 'datetime',
        'cierra_sin_stock' => 'boolean',
    ];


    // Relación con el detalle de orden
    public function detalleOrden() : BelongsTo
    {
        return $this->belongsTo(DetalleOrden::class, 'detalle_orden_id');
    }

    // Registrar devolución y restaurar stock
    public static function registrarDevolucion(int $detalleOrdenId, int $cantidadDevuelta, ?string $observaciones = null): self
    {
        return DB::transaction(function () use ($detalleOrdenId, $cantidadDevuelta, $observaciones): self {
            $detalleOrden = DetalleOrden::with(['producto.tipoProducto.parametro', 'devoluciones'])
                ->findOrFail($detalleOrdenId);

            self::validarDevolucion($detalleOrden, $cantidadDevuelta);

            $esCierreSinStock = $cantidadDevuelta === 0;
            $observacionesDepuradas = $observaciones !== null ? trim($observaciones) : null;

            if ($esCierreSinStock) {
                self::validarCierreSinStock($detalleOrden, $observacionesDepuradas);
            }

            $devolucion = self::crearDevolucion($detalleOrdenId, $cantidadDevuelta, $observacionesDepuradas, $esCierreSinStock);

            self::procesarDevolucionStock($detalleOrden, $esCierreSinStock, $cantidadDevuelta);

            return $devolucion->fresh([
                'detalleOrden.producto',
                'detalleOrden.orden',
            ]);
        });
    }

    private static function validarDevolucion(DetalleOrden $detalleOrden, int $cantidadDevuelta): void
    {
        if ($detalleOrden->tieneCierreSinStock()) {
            throw new DevolucionException('Este préstamo ya fue cerrado sin devolución de stock.');
        }

        // Si la cantidad es 0, se permite cerrar sin stock (validación aparte)
        if ($cantidadDevuelta === 0) {
            return;
        }

        $cantidadPendiente = $detalleOrden->getCantidadPendiente();
        if ($cantidadPendiente <= 0) {
            throw new DevolucionException('No hay cantidades pendientes por devolver.');
        }

        if ($cantidadDevuelta < 0) {
            throw new DevolucionException('La cantidad devuelta no puede ser negativa.');
        }

        if ($cantidadDevuelta > $cantidadPendiente) {
            throw new DevolucionException("No puedes devolver más de lo prestado. Cantidad pendiente: {$cantidadPendiente}");
        }
    }

    private static function validarCierreSinStock(DetalleOrden $detalleOrden, ?string $observacionesDepuradas): void
    {
        if ($observacionesDepuradas === null || $observacionesDepuradas === '') {
            throw new DevolucionException('Debes registrar el motivo del consumo total para cerrar sin devolución.');
        }

        if (!$detalleOrden->producto->esConsumible()) {
            throw new DevolucionException('Solo los productos consumibles pueden cerrarse sin devolución de stock.');
        }
    }

    private static function crearDevolucion(int $detalleOrdenId, int $cantidadDevuelta, ?string $observacionesDepuradas, bool $esCierreSinStock): self
    {
        return self::create([
            'detalle_orden_id' => $detalleOrdenId,
            'cantidad_devuelta' => $cantidadDevuelta,
            'fecha_devolucion' => now(),
            'estado_id' => 1,
            'observaciones' => $observacionesDepuradas,
            'cierra_sin_stock' => $esCierreSinStock,
            'user_create_id' => Auth::id(),
            'user_update_id' => Auth::id()
        ]);
    }

    private static function procesarDevolucionStock(DetalleOrden $detalleOrden, bool $esCierreSinStock, int $cantidadDevuelta): void
    {
        if (!$esCierreSinStock && $cantidadDevuelta > 0) {
            $detalleOrden->producto->devolverStock($cantidadDevuelta);
        }
    }


    // Verificar si la devolución fue a tiempo
    public function fueATiempo() : ?bool
    {
        $fechaEsperada = $this->detalleOrden->orden->fecha_devolucion;

        if (!$fechaEsperada) {
            return null;
        }

        return $this->fecha_devolucion->lte($fechaEsperada);
    }


    //Obtener días de retraso en la devolución
    public function getDiasRetraso() : int
    {
        $fechaEsperada = $this->detalleOrden->orden->fecha_devolucion;

        if (!$fechaEsperada || $this->fueATiempo()) {
            return 0;
        }

        // Si la devolución fue antes de la fecha esperada, no hay retraso
        if ($this->fecha_devolucion->lt($fechaEsperada)) {
            return 0;
        }

        // Calcular días de retraso (siempre positivo)
        $dias = $this->fecha_devolucion->diffInDays($fechaEsperada, false);
        return (int) max(0, $dias);
    }

    // Alias para compatibilidad con el controlador
    public function getDiasRetrasoDevolucion() : int
    {
        return $this->getDiasRetraso();
    }
}
