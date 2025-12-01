<?php

namespace Database\Factories\Inventario;

use App\Exceptions\InventarioFactoryException;
use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Orden;
use App\Models\Inventario\Producto;
use Database\Factories\Concerns\HasParametroTema;
use Database\Factories\Concerns\HasUserId;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\DetalleOrden>
 */
class DetalleOrdenFactory extends Factory
{
    use HasUserId, HasParametroTema;

    protected $model = DetalleOrden::class;

    public function definition(): array
    {
        // Obtener o crear orden
        $ordenId = null;
        try {
            $ordenId = Orden::query()->inRandomOrder()->value('id');
        } catch (\Exception $e) {
            // Ignorar error de consulta
        }

        if (!$ordenId) {
            try {
                $ordenId = Orden::factory()->create()->id;
            } catch (\Throwable $e) {
                throw new InventarioFactoryException(
                    'No se pudo crear una Orden para el DetalleOrden. Error: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        // Obtener o crear producto
        $productoId = null;
        try {
            $productoId = Producto::query()->inRandomOrder()->value('id');
        } catch (\Exception $e) {
            // Ignorar error de consulta
        }

        if (!$productoId) {
            try {
                $productoId = Producto::factory()->create()->id;
            } catch (\Throwable $e) {
                throw new InventarioFactoryException(
                    'No se pudo crear un Producto para el DetalleOrden. Error: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        // Obtener estado_orden_id de parametros_temas - campo NOT NULL
        $estadoOrdenId = $this->obtenerParametroTemaAleatorio();

        return [
            'orden_id' => $ordenId,
            'producto_id' => $productoId,
            'cantidad' => rand(1, 10),
            'estado_orden_id' => $estadoOrdenId,
            'user_create_id' => $this->getUserId(),
            'user_update_id' => $this->getUserId(),
        ];
    }

}


