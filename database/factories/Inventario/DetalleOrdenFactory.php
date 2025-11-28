<?php

namespace Database\Factories\Inventario;

use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Orden;
use App\Models\Inventario\Producto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\DetalleOrden>
 */
class DetalleOrdenFactory extends Factory
{
    protected $model = DetalleOrden::class;

    public function definition(): array
    {
        return [
            'orden_id' => Orden::factory(),
            'producto_id' => Producto::factory(),
            'cantidad' => rand(1, 10),
            'estado_orden_id' => [46, 47, 48][array_rand([46, 47, 48])],
            'user_create_id' => $this->getUserId(),
            'user_update_id' => $this->getUserId(),
        ];
    }

    private function getUserId(): int
    {
        if (!Schema::hasTable('users')) {
            return 1;
        }

        try {
            $userId = User::query()->inRandomOrder()->value('id');
            return $userId ?? User::factory()->create()->id;
        } catch (\Exception $e) {
            return User::factory()->create()->id;
        }
    }
}


