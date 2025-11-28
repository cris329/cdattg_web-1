<?php

namespace Database\Factories\Inventario;

use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Devolucion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\Devolucion>
 */
class DevolucionFactory extends Factory
{
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

        $userId = config('app.audit_default_user_id', 1);
        if (Schema::hasTable('users')) {
            try {
                $userId = User::query()->inRandomOrder()->value('id') ?? $userId;
            } catch (\Exception $e) {
                $userId = config('app.audit_default_user_id', 1);
            }
        }

        $cantidadDevuelta = $this->faker->numberBetween(1, 10);
        $cierraSinStock = $this->faker->boolean(10);

        return [
            'detalle_orden_id' => $detalleOrdenId,
            'cantidad_devuelta' => $cierraSinStock ? 0 : $cantidadDevuelta,
            'fecha_devolucion' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'estado_id' => 1,
            'observaciones' => $cierraSinStock ? $this->faker->sentence() : $this->faker->optional()->sentence(),
            'cierra_sin_stock' => $cierraSinStock,
            'user_create_id' => $userId,
            'user_update_id' => $userId,
        ];
    }
}
