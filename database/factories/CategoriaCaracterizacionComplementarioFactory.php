<?php

namespace Database\Factories;

use App\Models\CategoriaCaracterizacionComplementario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CategoriaCaracterizacionComplementario>
 */
class CategoriaCaracterizacionComplementarioFactory extends Factory
{
    protected $model = CategoriaCaracterizacionComplementario::class;

    public function definition(): array
    {
        $nombre = $this->faker->unique()->words(2, true);
        
        return [
            'nombre' => ucwords($nombre),
            'slug' => \Illuminate\Support\Str::slug($nombre),
            'activo' => $this->faker->boolean(80), // 80% probabilidad de estar activo
            'parent_id' => null,
        ];
    }

    /**
     * Categoría activa
     */
    public function activa(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => 1,
        ]);
    }

    /**
     * Categoría inactiva
     */
    public function inactiva(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => 0,
        ]);
    }

    /**
     * Categoría con padre
     */
    public function conPadre(?int $parentId = null): static
    {
        return $this->state(function (array $attributes) use ($parentId) {
            if ($parentId === null) {
                $parent = CategoriaCaracterizacionComplementario::factory()->create();
                $parentId = $parent->id;
            }
            
            return [
                'parent_id' => $parentId,
            ];
        });
    }
}

