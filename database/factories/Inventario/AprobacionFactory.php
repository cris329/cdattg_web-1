<?php

namespace Database\Factories\Inventario;

use App\Models\Inventario\Aprobacion;
use App\Models\Inventario\DetalleOrden;
use Database\Factories\Concerns\HasUserId;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\Aprobacion>
 */
class AprobacionFactory extends Factory
{
    use HasUserId;

    protected $model = Aprobacion::class;

    public function definition(): array
    {
        return [
            'detalle_orden_id' => DetalleOrden::factory(),
            'estado_aprobacion_id' => [49, 50][array_rand([49, 50])],
            'user_create_id' => $this->getUserId(),
            'user_update_id' => $this->getUserId(),
        ];
    }
}


