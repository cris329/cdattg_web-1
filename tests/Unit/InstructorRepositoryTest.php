<?php

namespace Tests\Unit;

use App\Models\Instructor;
use App\Models\Persona;
use App\Repositories\InstructorRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstructorRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected InstructorRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);

        $this->repository = app(InstructorRepository::class);
    }

    #[Test]
    public function puede_obtener_instructor_por_persona_id(): void
    {
        $persona = Persona::factory()->create();
        $instructor = Instructor::factory()->create(['persona_id' => $persona->id]);

        $resultado = $this->repository->getInstructor($persona->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($instructor->id, $resultado->id);
    }

    #[Test]
    public function retorna_null_si_instructor_no_existe(): void
    {
        $persona = Persona::factory()->create();

        $resultado = $this->repository->getInstructor($persona->id);

        $this->assertNull($resultado);
    }
}
