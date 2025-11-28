<?php

namespace Tests\Unit;

use App\Models\ProgramaFormacion;
use App\Repositories\ProgramaFormacionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProgramaFormacionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ProgramaFormacionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        $this->repository = app(ProgramaFormacionRepository::class);
    }

    #[Test]
    public function puede_obtener_programas_activos(): void
    {
        ProgramaFormacion::factory()->count(5)->create(['status' => true]);
        ProgramaFormacion::factory()->count(2)->create(['status' => false]);

        $resultado = $this->repository->obtenerActivos();

        $this->assertCount(5, $resultado);
        $resultado->each(function ($programa) {
            $this->assertTrue($programa->status);
        });
    }

    #[Test]
    public function puede_buscar_programas_por_termino(): void
    {
        ProgramaFormacion::factory()->create([
            'nombre' => 'Programa de Prueba',
            'status' => true,
        ]);

        $resultado = $this->repository->buscar('Prueba');

        $this->assertNotEmpty($resultado);
        $this->assertStringContainsString('Prueba', $resultado->first()->nombre);
    }

    #[Test]
    public function puede_obtener_programas_por_red(): void
    {
        $red = \App\Models\RedConocimiento::first();
        ProgramaFormacion::factory()->create([
            'red_conocimiento_id' => $red->id,
            'status' => true,
        ]);

        $resultado = $this->repository->obtenerPorRed($red->id);

        $this->assertNotEmpty($resultado);
        $resultado->each(function ($programa) use ($red) {
            $this->assertEquals($red->id, $programa->red_conocimiento_id);
        });
    }
}
