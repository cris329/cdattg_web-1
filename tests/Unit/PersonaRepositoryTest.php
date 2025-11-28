<?php

namespace Tests\Unit;

use App\Models\Persona;
use App\Models\User;
use App\Repositories\PersonaRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonaRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected PersonaRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);

        $this->repository = app(PersonaRepository::class);
    }

    #[Test]
    public function puede_obtener_persona_por_usuario(): void
    {
        $persona = Persona::factory()->create();
        $user = User::factory()->create(['persona_id' => $persona->id]);

        $resultado = $this->repository->getPersona($user);

        $this->assertNotNull($resultado);
        $this->assertEquals($persona->id, $resultado->id);
    }
}
