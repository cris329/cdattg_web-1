<?php

namespace Tests\Unit;

use App\Models\ProgramaFormacion;
use App\Services\ProgramaFormacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProgramaFormacionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProgramaFormacionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        $this->service = app(ProgramaFormacionService::class);
    }

    #[Test]
    public function puede_listar_programas(): void
    {
        ProgramaFormacion::factory()->count(10)->create();

        $resultado = $this->service->listar(5);

        $this->assertCount(5, $resultado->items());
        $this->assertEquals(10, $resultado->total());
    }

    #[Test]
    public function puede_obtener_programas_activos(): void
    {
        ProgramaFormacion::factory()->count(5)->create(['status' => true]);
        ProgramaFormacion::factory()->count(2)->create(['status' => false]);

        $resultado = $this->service->obtenerActivos();

        $this->assertCount(5, $resultado);
        $resultado->each(function ($programa) {
            $this->assertTrue($programa->status);
        });
    }

    #[Test]
    public function puede_crear_programa(): void
    {
        $red = \App\Models\RedConocimiento::first();

        $datos = [
            'nombre' => 'Programa Test',
            'codigo' => 'PROG-TEST',
            'red_conocimiento_id' => $red->id,
            'status' => true,
        ];

        $programa = $this->service->crear($datos);

        $this->assertInstanceOf(ProgramaFormacion::class, $programa);
        $this->assertDatabaseHas('programas_formacion', [
            'nombre' => 'Programa Test',
        ]);
    }
}
