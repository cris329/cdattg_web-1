<?php

namespace Database\Factories\Inventario;

use App\Exceptions\InventarioFactoryException;
use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Devolucion;
use App\Models\User;
use Database\Factories\Concerns\HasParametroTema;
use Database\Factories\Concerns\HasUserId;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\Devolucion>
 */
class DevolucionFactory extends Factory
{
    use HasUserId, HasParametroTema;

    protected $model = Devolucion::class;

    public function definition(): array
    {
        $detalleOrdenId = 1;
        if (Schema::hasTable('detalle_ordenes')) {
            try {
                $detalleOrdenId = DetalleOrden::query()->inRandomOrder()->value('id');
                if (! $detalleOrdenId) {
                    $detalleOrdenId = DetalleOrden::factory()->create()->id;
                }
            } catch (\Exception $e) {
                $detalleOrdenId = DetalleOrden::factory()->create()->id;
            }
        }

        // Obtener estado_id de parametros_temas - campo NOT NULL
        $estadoId = $this->obtenerParametroTemaAleatorio();

        // Obtener user_id usando HasUserId
        $userId = $this->getUserId();

        $cantidadDevuelta = $this->faker->numberBetween(1, 10);
        $cierraSinStock = $this->faker->boolean(10);

        return [
            'detalle_orden_id' => $detalleOrdenId,
            'cantidad_devuelta' => $cierraSinStock ? 0 : $cantidadDevuelta,
            'fecha_devolucion' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'estado_id' => $estadoId,
            'observaciones' => $cierraSinStock ? $this->faker->sentence() : $this->faker->optional()->sentence(),
            'cierra_sin_stock' => $cierraSinStock,
            'user_create_id' => $userId,
            'user_update_id' => $userId,
        ];
    }

}
