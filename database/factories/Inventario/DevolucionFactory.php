<?php

namespace Database\Factories\Inventario;

use App\Models\Inventario\DetalleOrden;
use App\Models\Inventario\Devolucion;
use App\Models\User;
use Database\Factories\Concerns\HasUserId;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\Devolucion>
 */
class DevolucionFactory extends Factory
{
    use HasUserId;

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

    /**
     * Obtiene un parametro_tema aleatorio o crea uno básico si no existe ninguno
     */
    private function obtenerParametroTemaAleatorio(): int
    {
        try {
            $parametroTemaId = \App\Models\ParametroTema::query()->inRandomOrder()->value('id');
            if ($parametroTemaId) {
                return $parametroTemaId;
            }
        } catch (\Exception $e) {
            // Ignorar error de consulta
        }

        // Si no hay parametros_temas, intentar crear uno básico
        try {
            $tema = \App\Models\Tema::query()->inRandomOrder()->first();
            $parametro = \App\Models\Parametro::query()->inRandomOrder()->first();
            
            // Si no hay tema, crear uno básico (user_create_id y user_edit_id son nullable)
            if (!$tema) {
                $tema = \App\Models\Tema::query()->create([
                    'name' => 'TEMA FACTORY ' . uniqid(),
                    'status' => 1,
                    'user_create_id' => null,
                    'user_edit_id' => null,
                ]);
            }
            
            // Si no hay parametro, crear uno básico (user_create_id y user_edit_id son nullable)
            if (!$parametro) {
                $parametro = \App\Models\Parametro::query()->create([
                    'name' => 'PARAMETRO FACTORY ' . uniqid(),
                    'status' => 1,
                    'user_create_id' => null,
                    'user_edit_id' => null,
                ]);
            }
            
            // Crear el parametro_tema
            $tema->parametros()->syncWithoutDetaching([
                $parametro->id => ['status' => 1]
            ]);
            
            $parametroTema = \App\Models\ParametroTema::query()
                ->where('tema_id', $tema->id)
                ->where('parametro_id', $parametro->id)
                ->orderBy('id', 'desc')
                ->first();
            
            if ($parametroTema) {
                return $parametroTema->id;
            }
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'No se encontró ningún parametro_tema y no se pudo crear uno. ' .
                'Error: ' . $e->getMessage() . '. ' .
                'Ejecuta los seeders necesarios (TemaSeeder, ParametroSeeder).',
                0,
                $e
            );
        }

        throw new \RuntimeException(
            'No se encontró ningún parametro_tema y no se pudo crear uno. ' .
            'Ejecuta los seeders necesarios (TemaSeeder, ParametroSeeder).'
        );
    }
}
