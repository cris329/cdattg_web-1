<?php

namespace Tests\Unit\Repositories;

use App\Models\GuiasAprendizaje;
use App\Repositories\GuiasAprendizajeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuiasAprendizajeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected GuiasAprendizajeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        $this->repository = new GuiasAprendizajeRepository;
    }

    #[Test]
    public function puede_obtener_guias_activas(): void
    {
        GuiasAprendizaje::factory()->count(3)->create(['status' => true]);
        GuiasAprendizaje::factory()->count(2)->create(['status' => false]);

        $resultado = $this->repository->obtenerActivas();

        $this->assertCount(3, $resultado);
        $resultado->each(function ($guia) {
            $this->assertTrue($guia->status);
        });
    }

    #[Test]
    public function puede_obtener_guias_por_programa(): void
    {
        $programa = \App\Models\ProgramaFormacion::factory()->create();
        GuiasAprendizaje::factory()->count(2)->create([
            'programa_formacion_id' => $programa->id,
        ]);

        $resultado = $this->repository->obtenerPorPrograma($programa->id);

        $this->assertCount(2, $resultado);
    }
}
