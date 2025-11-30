<?php

namespace Database\Factories\Inventario;

use App\Models\Inventario\Marca;
use App\Models\ParametroTema;
use App\Models\Tema;
use App\Models\User;
use Database\Factories\Concerns\HasUserId;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\Marca>
 */
class MarcaFactory extends Factory
{
    use HasUserId;

    protected $model = Marca::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombreMarca = $this->faker->unique()->company();
        
        $userId = null;
        try {
            $userId = $this->getUserId();
        } catch (\Exception $e) {
            // Si no se puede obtener usuario, usar null (campo es nullable)
            $userId = null;
        }

        return [
            'name' => strtoupper($nombreMarca),
            'status' => $this->faker->boolean(90),
            'user_create_id' => $userId,
            'user_edit_id' => $userId,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Marca $marca): void {
            // Asociar automáticamente al tema MARCAS
            $tema = Marca::tema();
            
            if ($tema) {
                // Verificar si ya existe la asociación
                $existeAsociacion = ParametroTema::query()
                    ->where('parametro_id', $marca->id)
                    ->where('tema_id', $tema->id)
                    ->exists();

                if (!$existeAsociacion) {
                    ParametroTema::create([
                        'parametro_id' => $marca->id,
                        'tema_id' => $tema->id,
                        'status' => 1,
                        'user_create_id' => $marca->user_create_id,
                        'user_edit_id' => $marca->user_edit_id,
                    ]);
                }
            }
        });
    }
}

