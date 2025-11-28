<?php

namespace Database\Factories\Inventario;

use App\Models\Inventario\Orden;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\Orden>
 */
class OrdenFactory extends Factory
{
    protected $model = Orden::class;

    public function definition(): array
    {
        $tipoOrdenId = [44, 45][array_rand([44, 45])];
        
        $diasAdelante = rand(7, 120);
        $fechaDevolucion = $tipoOrdenId === 44
            ? date('Y-m-d', strtotime("+{$diasAdelante} days"))
            : null;

        $descripciones = [
            'ORDEN DE COMPRA EQUIPOS TECNOLÓGICOS',
            'ORDEN DE SUMINISTRO MATERIALES',
            'ORDEN DE SERVICIO MANTENIMIENTO',
            'ORDEN DE COMPRA HERRAMIENTAS',
        ];

        return [
            'descripcion_orden' => strtoupper($descripciones[array_rand($descripciones)]),
            'tipo_orden_id' => $tipoOrdenId,
            'fecha_devolucion' => $fechaDevolucion,
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


