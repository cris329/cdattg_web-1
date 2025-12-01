<?php


namespace Database\Factories;

use App\Exceptions\UserFactoryException;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uniqueId = uniqid('user_', true);
        $timestamp = time() . rand(1000, 9999);
        
        // Asegurar que persona_id nunca sea null
        $personaId = null;
        
        try {
            $personaId = Persona::query()->inRandomOrder()->value('id');
        } catch (\Throwable $e) {
            // Ignorar error de consulta, se creará una nueva persona
        }
        
        if (!$personaId) {
            try {
                $persona = Persona::factory()->create();
                $personaId = $persona->id;
            } catch (\Throwable $e) {
                // Si falla la creación del factory, lanzar excepción en lugar de establecer null
                throw new UserFactoryException(
                    'No se pudo crear una Persona para el User. Error: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }
        
        // Validación final: asegurar que persona_id no sea null
        if (!$personaId) {
            throw new UserFactoryException('persona_id no puede ser null. La tabla users requiere persona_id NOT NULL.');
        }
        
        return [
            'email' => strtolower($uniqueId . $timestamp . '@example.com'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('12345678'),
            'remember_token' => Str::random(10),
            'status' => 1,
            'persona_id' => $personaId,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'status' => 0,
        ]);
    }

    public function role(string $role): static
    {
        return $this->afterCreating(function (User $user) use ($role) {
            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }
        });
    }

    public function forPersona(Persona $persona): static
    {
        return $this->state(fn () => ['persona_id' => $persona->id]);
    }
}
