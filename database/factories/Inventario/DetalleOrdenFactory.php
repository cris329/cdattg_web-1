<?php

namespace Database\Factories\Inventario;

use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Orden;
use App\Models\Inventario\Producto;
use Database\Factories\Concerns\HasUserId;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\DetalleOrden>
 */
class DetalleOrdenFactory extends Factory
{
    use HasUserId;

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
}


