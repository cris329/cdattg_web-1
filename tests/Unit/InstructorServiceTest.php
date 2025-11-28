<?php

namespace Tests\Unit;

use App\Models\Instructor;
use App\Models\Persona;
use App\Models\User;
use App\Services\InstructorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstructorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InstructorService $service;

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

        $this->service = app(InstructorService::class);
    }

    #[Test]
    public function puede_listar_instructores_con_filtros(): void
    {
        Instructor::factory()->count(10)->create();

        $filtros = ['per_page' => 5];
        $resultado = $this->service->listarConFiltros($filtros);

        $this->assertCount(5, $resultado->items());
        $this->assertEquals(10, $resultado->total());
    }

    #[Test]
    public function puede_obtener_instructor_por_id(): void
    {
        $instructor = Instructor::factory()->create();

        $resultado = $this->service->obtener($instructor->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($instructor->id, $resultado->id);
    }

    #[Test]
    public function puede_crear_instructor(): void
    {
        $persona = Persona::factory()->create();
        $user = User::factory()->create(['persona_id' => $persona->id]);
        $regional = \App\Models\Regional::first();

        $datos = [
            'persona_id' => $persona->id,
            'regional_id' => $regional->id,
            'anos_experiencia' => 5,
        ];

        $instructor = $this->service->crear($datos);

        $this->assertInstanceOf(Instructor::class, $instructor);
        $this->assertDatabaseHas('instructors', [
            'persona_id' => $persona->id,
        ]);
    }

    #[Test]
    public function no_puede_crear_instructor_si_persona_ya_es_instructor(): void
    {
        $instructor = Instructor::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Esta persona ya es instructor.');

        $datos = [
            'persona_id' => $instructor->persona_id,
            'regional_id' => $instructor->regional_id,
        ];

        $this->service->crear($datos);
    }
}
