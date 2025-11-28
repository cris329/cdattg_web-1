<?php

namespace Database\Factories\Inventario;

use App\Models\Inventario\Aprobacion;
use App\Models\Inventario\DetalleOrden;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\Aprobacion>
 */
class AprobacionFactory extends Factory
{
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


