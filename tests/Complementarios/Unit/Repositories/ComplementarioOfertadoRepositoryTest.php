<?php

namespace Tests\Complementarios\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\Complementarios\ComplementarioOfertadoRepository;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\ParametroTema;
use App\Models\JornadaFormacion;
use App\Models\Ambiente;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComplementarioOfertadoRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_PROGRAMA_NOMBRE = 'Test Programa';

    protected ComplementarioOfertadoRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\PersonaSeeder::class,
            \Database\Seeders\UsersSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
            \Database\Seeders\CentroFormacionSeeder::class,
            \Database\Seeders\SedeSeeder::class,
            \Database\Seeders\BloqueSeeder::class,
            \Database\Seeders\PisoSeeder::class,
            \Database\Seeders\AmbienteSeeder::class,
            \Database\Seeders\JornadaFormacionSeeder::class,
        ]);

        $this->repository = new ComplementarioOfertadoRepository();
    }

    /** @test */
    public function puede_obtener_todos_los_programas()
    {
        ComplementarioOfertado::factory()->count(5)->create();

        $programas = $this->repository->getAll();

        $this->assertCount(5, $programas);
    }

    /** @test */
    public function puede_obtener_programas_por_estado()
    {
        ComplementarioOfertado::factory()->count(3)->conOferta()->create();
        ComplementarioOfertado::factory()->count(2)->sinOferta()->create();

        $activos = $this->repository->getByEstado(1);
        $sinOferta = $this->repository->getByEstado(0);

        $this->assertCount(3, $activos);
        $this->assertCount(2, $sinOferta);
    }

    /** @test */
    public function puede_obtener_programas_activos()
    {
        ComplementarioOfertado::factory()->count(4)->conOferta()->create();
        ComplementarioOfertado::factory()->count(2)->sinOferta()->create();

        $activos = $this->repository->getActivos();

        $this->assertCount(4, $activos);
        $activos->each(function ($programa) {
            $this->assertEquals(1, $programa->estado);
        });
    }

    /** @test */
    public function puede_buscar_programa_por_id()
    {
        $programa = ComplementarioOfertado::factory()->create();

        $encontrado = $this->repository->findWithRelations($programa->id);

        $this->assertNotNull($encontrado);
        $this->assertEquals($programa->id, $encontrado->id);
    }

    /** @test */
    public function puede_buscar_programa_por_nombre()
    {
        $programa = ComplementarioOfertado::factory()->create(['nombre' => 'Auxiliar de Cocina']);

        $encontrado = $this->repository->findByNombre('Auxiliar-de-Cocina');

        $this->assertNotNull($encontrado);
        $this->assertEquals($programa->id, $encontrado->id);
    }

    /** @test */
    public function puede_obtener_programas_con_conteo_de_aspirantes()
    {
        $programa1 = ComplementarioOfertado::factory()->create();
        $programa2 = ComplementarioOfertado::factory()->create();

        AspiranteComplementario::factory()->count(5)->paraPrograma($programa1)->create();
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa2)->create();

        $programas = $this->repository->getAllWithAspirantesCount();

        $programa1Data = $programas->firstWhere('id', $programa1->id);
        $programa2Data = $programas->firstWhere('id', $programa2->id);

        $this->assertEquals(5, $programa1Data->aspirantes_count);
        $this->assertEquals(3, $programa2Data->aspirantes_count);
    }

    /** @test */
    public function puede_crear_programa()
    {
        // Obtener IDs válidos de las tablas relacionadas
        $modalidad = ParametroTema::where('tema_id', 5)
            ->whereIn('parametro_id', [18, 19, 20])
            ->first();
        
        $jornada = JornadaFormacion::first();
        $ambiente = Ambiente::first();

        $data = [
            'codigo' => 'COMP0001',
            'nombre' => self::TEST_PROGRAMA_NOMBRE,
            'justificacion' => 'Justificación de prueba',
            'requisitos_ingreso' => 'Requisitos de prueba',
            'duracion' => 60,
            'cupos' => 30,
            'estado' => 1,
            'modalidad_id' => $modalidad->id ?? 1,
            'jornada_id' => $jornada->id ?? 1,
            'ambiente_id' => $ambiente->id ?? 1,
        ];

        $programa = $this->repository->create($data);

        $this->assertDatabaseHas('complementarios_ofertados', [
            'codigo' => 'COMP0001',
            'nombre' => self::TEST_PROGRAMA_NOMBRE,
        ]);
        $this->assertEquals(self::TEST_PROGRAMA_NOMBRE, $programa->nombre);
    }

    /** @test */
    public function puede_actualizar_programa()
    {
        $programa = ComplementarioOfertado::factory()->create();

        $actualizado = $this->repository->update($programa, ['nombre' => 'Nombre Actualizado']);

        $this->assertTrue($actualizado);
        $this->assertDatabaseHas('complementarios_ofertados', [
            'id' => $programa->id,
            'nombre' => 'Nombre Actualizado',
        ]);
    }

    /** @test */
    public function puede_eliminar_programa()
    {
        $programa = ComplementarioOfertado::factory()->create();

        $eliminado = $this->repository->delete($programa);

        $this->assertTrue($eliminado);
        $this->assertDatabaseMissing('complementarios_ofertados', [
            'id' => $programa->id,
        ]);
    }

    /** @test */
    public function puede_contar_programas_activos()
    {
        ComplementarioOfertado::factory()->count(5)->conOferta()->create();
        ComplementarioOfertado::factory()->count(3)->sinOferta()->create();

        $count = $this->repository->countActivos();

        $this->assertEquals(5, $count);
    }

    /** @test */
    public function puede_obtener_estadisticas()
    {
        ComplementarioOfertado::factory()->count(3)->conOferta()->create();
        ComplementarioOfertado::factory()->count(2)->sinOferta()->create();
        ComplementarioOfertado::factory()->count(1)->cuposLlenos()->create();

        $estadisticas = $this->repository->getEstadisticas();

        $this->assertEquals(6, $estadisticas['total']);
        $this->assertEquals(3, $estadisticas['activos']);
        $this->assertEquals(2, $estadisticas['sin_oferta']);
        $this->assertEquals(1, $estadisticas['cupos_llenos']);
    }

    /** @test */
    public function puede_obtener_programas_con_mayor_demanda()
    {
        $programa1 = ComplementarioOfertado::factory()->create();
        $programa2 = ComplementarioOfertado::factory()->create();

        AspiranteComplementario::factory()->count(10)->paraPrograma($programa1)->create();
        AspiranteComplementario::factory()->count(5)->paraPrograma($programa2)->create();

        $programas = $this->repository->getProgramasConMayorDemanda(10);

        $this->assertGreaterThanOrEqual(2, $programas->count());
        $programa1Data = $programas->firstWhere('id', $programa1->id);
        $this->assertNotNull($programa1Data);
        $this->assertEquals(10, $programa1Data->total_aspirantes);
    }
}
