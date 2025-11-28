<?php

namespace Tests\Unit\Repositories;

use App\Models\Aprendiz;
use App\Models\AprendizFicha;
use App\Models\FichaCaracterizacion;
use App\Repositories\AprendizFichaRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AprendizFichaRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AprendizFichaRepository $repository;

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

        $this->repository = new AprendizFichaRepository;
    }

    #[Test]
    public function obtiene_fichas_por_aprendiz(): void
    {
        $aprendiz = Aprendiz::factory()->create();
        $fichas = FichaCaracterizacion::factory()->count(2)->create();

        foreach ($fichas as $ficha) {
            AprendizFicha::factory()->create([
                'aprendiz_id' => $aprendiz->id,
                'ficha_id' => $ficha->id,
            ]);
        }

        $resultado = $this->repository->obtenerPorAprendiz($aprendiz->id);

        $this->assertCount(2, $resultado);
        foreach ($resultado as $aprendizFicha) {
            $this->assertEquals($aprendiz->id, $aprendizFicha->aprendiz_id);
        }
    }

    #[Test]
    public function crea_asignacion_aprendiz_ficha(): void
    {
        $aprendiz = Aprendiz::factory()->create();
        $ficha = FichaCaracterizacion::factory()->create();

        $datos = [
            'aprendiz_id' => $aprendiz->id,
            'ficha_id' => $ficha->id,
        ];

        $aprendizFicha = $this->repository->crear($datos);

        $this->assertDatabaseHas('aprendiz_fichas_caracterizacion', [
            'aprendiz_id' => $aprendiz->id,
            'ficha_id' => $ficha->id,
        ]);
        $this->assertEquals($aprendiz->id, $aprendizFicha->aprendiz_id);
    }

    #[Test]
    public function elimina_asignacion(): void
    {
        $aprendizFicha = AprendizFicha::factory()->create();

        $eliminado = $this->repository->eliminar($aprendizFicha->id);

        $this->assertTrue($eliminado);
        $this->assertDatabaseMissing('aprendiz_fichas_caracterizacion', [
            'id' => $aprendizFicha->id,
        ]);
    }

    #[Test]
    public function retorna_false_si_aprendiz_no_esta_en_ficha(): void
    {
        $aprendiz = Aprendiz::factory()->create();
        $ficha = FichaCaracterizacion::factory()->create();

        $estaEnFicha = $this->repository->estaEnFicha($aprendiz->id, $ficha->id);

        $this->assertFalse($estaEnFicha);
    }

    #[Test]
    public function retorna_coleccion_vacia_si_no_hay_fichas(): void
    {
        $aprendiz = Aprendiz::factory()->create();

        $resultado = $this->repository->obtenerPorAprendiz($aprendiz->id);

        $this->assertCount(0, $resultado);
    }
}
