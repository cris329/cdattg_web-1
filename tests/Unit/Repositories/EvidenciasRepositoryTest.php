<?php

namespace Tests\Unit\Repositories;

use App\Models\Evidencias;
use App\Models\GuiasAprendizaje;
use App\Repositories\EvidenciasRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EvidenciasRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EvidenciasRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->repository = new EvidenciasRepository;
    }

    #[Test]
    public function obtiene_ultima_evidencia_por_guia_aprendizaje(): void
    {
        $guia = GuiasAprendizaje::factory()->create();
        $evidencias = Evidencias::factory()->count(3)->create();

        foreach ($evidencias as $index => $evidencia) {
            $evidencia->guiasAprendizaje()->attach($guia->id);
        }

        $resultado = $this->repository->getUltimaEvidencia($guia->id);

        $this->assertNotNull($resultado);
        $this->assertInstanceOf(Evidencias::class, $resultado);
    }

    #[Test]
    public function retorna_null_si_no_hay_evidencias(): void
    {
        $guia = GuiasAprendizaje::factory()->create();

        $resultado = $this->repository->getUltimaEvidencia($guia->id);

        $this->assertNull($resultado);
    }

    #[Test]
    public function retorna_la_evidencia_mas_reciente(): void
    {
        $guia = GuiasAprendizaje::factory()->create();
        
        $evidencia1 = Evidencias::factory()->create(['created_at' => now()->subDays(2)]);
        $evidencia2 = Evidencias::factory()->create(['created_at' => now()->subDay()]);
        $evidencia3 = Evidencias::factory()->create(['created_at' => now()]);

        $evidencia1->guiasAprendizaje()->attach($guia->id);
        $evidencia2->guiasAprendizaje()->attach($guia->id);
        $evidencia3->guiasAprendizaje()->attach($guia->id);

        $resultado = $this->repository->getUltimaEvidencia($guia->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($evidencia3->id, $resultado->id);
    }
}

