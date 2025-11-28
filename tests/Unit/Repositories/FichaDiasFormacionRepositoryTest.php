<?php

namespace Tests\Unit\Repositories;

use App\Models\FichaCaracterizacion;
use App\Models\FichaDiasFormacion;
use App\Models\Parametro;
use App\Repositories\FichaDiasFormacionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FichaDiasFormacionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FichaDiasFormacionRepository $repository;

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

        $this->repository = new FichaDiasFormacionRepository;
    }

    #[Test]
    public function crea_relacion_ficha_dia(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $parametro = Parametro::firstOrCreate(['name' => 'LUNES']);

        $datos = [
            'ficha_caracterizacion_id' => $ficha->id,
            'dia_formacion_id' => $parametro->id,
        ];

        $fichaDia = $this->repository->crear($datos);

        $this->assertDatabaseHas('ficha_dias_formacion', [
            'ficha_id' => $ficha->id,
        ]);
        $this->assertNotNull($fichaDia);
    }

    #[Test]
    public function elimina_dias_por_ficha(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $parametro = Parametro::firstOrCreate(['name' => 'MARTES']);

        FichaDiasFormacion::factory()->create([
            'ficha_id' => $ficha->id,
            'dia_id' => $parametro->id,
        ]);

        $eliminado = $this->repository->eliminarPorFicha($ficha->id);

        $this->assertTrue($eliminado);
    }

    #[Test]
    public function asigna_multiples_dias_a_ficha(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $parametro1 = Parametro::firstOrCreate(['name' => 'LUNES']);
        $parametro2 = Parametro::firstOrCreate(['name' => 'MARTES']);
        $parametro3 = Parametro::firstOrCreate(['name' => 'MIERCOLES']);

        $diasIds = [$parametro1->id, $parametro2->id, $parametro3->id];
        $count = $this->repository->asignarDias($ficha->id, $diasIds);

        $this->assertEquals(3, $count);
    }

    #[Test]
    public function retorna_coleccion_vacia_si_no_hay_dias(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();

        $resultado = $this->repository->obtenerPorFicha($ficha->id);

        $this->assertCount(0, $resultado);
    }
}
