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
            } catch (\Exception $e) {
                throw new \RuntimeException(
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
            } catch (\Exception $e) {
                throw new \RuntimeException(
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


