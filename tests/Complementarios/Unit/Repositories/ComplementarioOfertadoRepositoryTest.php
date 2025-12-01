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
use PHPUnit\Framework\Attributes\Test;
use Tests\Complementarios\Concerns\SeedsComplementariosDatabase;

class ComplementarioOfertadoRepositoryTest extends TestCase
{
    use RefreshDatabase;
    use SeedsComplementariosDatabase;

    private const TEST_PROGRAMA_NOMBRE = 'Test Programa';

    protected ComplementarioOfertadoRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedComplementariosDatabaseIfNeeded();

        $this->repository = new ComplementarioOfertadoRepository();
    }

    #[Test]
    public function puede_obtener_todos_los_programas()
    {
        ComplementarioOfertado::factory()->count(5)->create();

        $programas = $this->repository->getAll();

        $this->assertCount(5, $programas);
    }

    #[Test]
    public function puede_obtener_programas_por_estado()
    {
        ComplementarioOfertado::factory()->count(3)->conOferta()->create();
        ComplementarioOfertado::factory()->count(2)->sinOferta()->create();

        $activos = $this->repository->getByEstado(1);
        $sinOferta = $this->repository->getByEstado(0);

        $this->assertCount(3, $activos);
        $this->assertCount(2, $sinOferta);
    }

    #[Test]
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

    #[Test]
    public function puede_buscar_programa_por_id()
    {
        $programa = ComplementarioOfertado::factory()->create();

        $encontrado = $this->repository->findWithRelations($programa->id);

        $this->assertNotNull($encontrado);
        $this->assertEquals($programa->id, $encontrado->id);
    }

    #[Test]
    public function puede_buscar_programa_por_nombre()
    {
        $programa = ComplementarioOfertado::factory()->create(['nombre' => 'Auxiliar de Cocina']);

        $encontrado = $this->repository->findByNombre('Auxiliar-de-Cocina');

        $this->assertNotNull($encontrado);
        $this->assertEquals($programa->id, $encontrado->id);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function puede_eliminar_programa()
    {
        $programa = ComplementarioOfertado::factory()->create();

        $eliminado = $this->repository->delete($programa);

        $this->assertTrue($eliminado);
        $this->assertDatabaseMissing('complementarios_ofertados', [
            'id' => $programa->id,
        ]);
    }

    #[Test]
    public function puede_contar_programas_activos()
    {
        ComplementarioOfertado::factory()->count(5)->conOferta()->create();
        ComplementarioOfertado::factory()->count(3)->sinOferta()->create();

        $count = $this->repository->countActivos();

        $this->assertEquals(5, $count);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function puede_obtener_todos_los_programas_con_relaciones()
    {
        ComplementarioOfertado::factory()->count(3)->create();

        $programas = $this->repository->getAll(['modalidad', 'jornada']);

        $this->assertCount(3, $programas);
        $programas->each(function ($programa) {
            $this->assertTrue($programa->relationLoaded('modalidad'));
            $this->assertTrue($programa->relationLoaded('jornada'));
        });
    }

    #[Test]
    public function puede_obtener_programas_por_estado_con_relaciones()
    {
        ComplementarioOfertado::factory()->count(2)->conOferta()->create();
        ComplementarioOfertado::factory()->count(1)->sinOferta()->create();

        $activos = $this->repository->getByEstado(1, ['modalidad']);

        $this->assertCount(2, $activos);
        $activos->each(function ($programa) {
            $this->assertEquals(1, $programa->estado);
            $this->assertTrue($programa->relationLoaded('modalidad'));
        });
    }

    #[Test]
    public function puede_obtener_programas_activos_con_relaciones()
    {
        ComplementarioOfertado::factory()->count(3)->conOferta()->create();
        ComplementarioOfertado::factory()->count(2)->sinOferta()->create();

        $activos = $this->repository->getActivos(['jornada']);

        $this->assertCount(3, $activos);
        $activos->each(function ($programa) {
            $this->assertEquals(1, $programa->estado);
            $this->assertTrue($programa->relationLoaded('jornada'));
        });
    }

    #[Test]
    public function puede_buscar_programa_por_id_con_relaciones()
    {
        $programa = ComplementarioOfertado::factory()->create();

        $encontrado = $this->repository->findWithRelations($programa->id, ['modalidad', 'jornada']);

        $this->assertNotNull($encontrado);
        $this->assertEquals($programa->id, $encontrado->id);
        $this->assertTrue($encontrado->relationLoaded('modalidad'));
        $this->assertTrue($encontrado->relationLoaded('jornada'));
    }

    #[Test]
    public function find_with_relations_retorna_null_si_no_existe()
    {
        $encontrado = $this->repository->findWithRelations(99999);

        $this->assertNull($encontrado);
    }

    #[Test]
    public function findByNombre_reemplaza_guiones_por_espacios()
    {
        $programa = ComplementarioOfertado::factory()->create(['nombre' => 'Programa con Espacios']);

        $encontrado = $this->repository->findByNombre('Programa-con-Espacios');

        $this->assertNotNull($encontrado);
        $this->assertEquals($programa->id, $encontrado->id);
    }

    #[Test]
    public function findByNombre_retorna_null_si_no_existe()
    {
        $encontrado = $this->repository->findByNombre('Programa-Inexistente');

        $this->assertNull($encontrado);
    }

    #[Test]
    public function puede_obtener_programas_con_conteo_de_aspirantes_y_relaciones()
    {
        $programa = ComplementarioOfertado::factory()->create();
        AspiranteComplementario::factory()->count(7)->paraPrograma($programa)->create();

        $programas = $this->repository->getAllWithAspirantesCount(['modalidad']);

        $programaData = $programas->firstWhere('id', $programa->id);
        $this->assertEquals(7, $programaData->aspirantes_count);
        $this->assertTrue($programaData->relationLoaded('modalidad'));
    }

    #[Test]
    public function puede_obtener_programas_con_mayor_demanda_con_aceptados_y_pendientes()
    {
        $programa1 = ComplementarioOfertado::factory()->create();
        $programa2 = ComplementarioOfertado::factory()->create();

        // Programa 1: 10 aspirantes (5 aceptados, 3 en proceso, 2 completos)
        AspiranteComplementario::factory()->count(5)->paraPrograma($programa1)->admitido()->create();
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa1)->enProceso()->create();
        AspiranteComplementario::factory()->count(2)->paraPrograma($programa1)->completo()->create();

        // Programa 2: 5 aspirantes (2 aceptados, 3 en proceso)
        AspiranteComplementario::factory()->count(2)->paraPrograma($programa2)->admitido()->create();
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa2)->enProceso()->create();

        $programas = $this->repository->getProgramasConMayorDemanda(10);

        $programa1Data = $programas->firstWhere('id', $programa1->id);
        $programa2Data = $programas->firstWhere('id', $programa2->id);

        $this->assertNotNull($programa1Data);
        $this->assertEquals(10, $programa1Data->total_aspirantes);
        $this->assertEquals(5, $programa1Data->aceptados);
        $this->assertEquals(5, $programa1Data->pendientes); // 3 en proceso + 2 completos
        $this->assertGreaterThan(0, $programa1Data->tasa_aceptacion);

        $this->assertNotNull($programa2Data);
        $this->assertEquals(5, $programa2Data->total_aspirantes);
        $this->assertEquals(2, $programa2Data->aceptados);
    }

    #[Test]
    public function get_programas_con_mayor_demanda_respeta_limite()
    {
        $programas = ComplementarioOfertado::factory()->count(15)->create();
        
        $programas->each(function ($programa, $index) {
            AspiranteComplementario::factory()->count($index + 1)->paraPrograma($programa)->create();
        });

        $resultado = $this->repository->getProgramasConMayorDemanda(5);

        $this->assertLessThanOrEqual(5, $resultado->count());
    }

    #[Test]
    public function get_programas_con_mayor_demanda_ordena_por_total_aspirantes_desc()
    {
        $programa1 = ComplementarioOfertado::factory()->create();
        $programa2 = ComplementarioOfertado::factory()->create();
        $programa3 = ComplementarioOfertado::factory()->create();

        AspiranteComplementario::factory()->count(5)->paraPrograma($programa1)->create();
        AspiranteComplementario::factory()->count(10)->paraPrograma($programa2)->create();
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa3)->create();

        $resultado = $this->repository->getProgramasConMayorDemanda(10);

        $ids = $resultado->pluck('id')->toArray();
        $programa2Index = array_search($programa2->id, $ids);
        $programa1Index = array_search($programa1->id, $ids);
        $programa3Index = array_search($programa3->id, $ids);

        $this->assertLessThan($programa1Index, $programa2Index);
        $this->assertLessThan($programa3Index, $programa1Index);
    }

    #[Test]
    public function get_programas_con_mayor_demanda_calcula_tasa_aceptacion_correctamente()
    {
        $programa = ComplementarioOfertado::factory()->create();
        
        // 10 aspirantes, 4 aceptados = 40%
        AspiranteComplementario::factory()->count(4)->paraPrograma($programa)->admitido()->create();
        AspiranteComplementario::factory()->count(6)->paraPrograma($programa)->enProceso()->create();

        $resultado = $this->repository->getProgramasConMayorDemanda(10);

        $programaData = $resultado->firstWhere('id', $programa->id);
        $this->assertNotNull($programaData);
        $this->assertEquals(40.0, $programaData->tasa_aceptacion);
    }

    #[Test]
    public function get_programas_con_mayor_demanda_tasa_aceptacion_cero_si_no_hay_aspirantes()
    {
        $programa = ComplementarioOfertado::factory()->create();

        $resultado = $this->repository->getProgramasConMayorDemanda(10);

        $programaData = $resultado->firstWhere('id', $programa->id);
        if ($programaData) {
            $this->assertEquals(0, $programaData->tasa_aceptacion);
        }
    }

    #[Test]
    public function puede_obtener_estadisticas_con_diferentes_estados()
    {
        ComplementarioOfertado::factory()->count(4)->conOferta()->create();
        ComplementarioOfertado::factory()->count(3)->sinOferta()->create();
        ComplementarioOfertado::factory()->count(2)->cuposLlenos()->create();

        $estadisticas = $this->repository->getEstadisticas();

        $this->assertEquals(9, $estadisticas['total']);
        $this->assertEquals(4, $estadisticas['activos']);
        $this->assertEquals(3, $estadisticas['sin_oferta']);
        $this->assertEquals(2, $estadisticas['cupos_llenos']);
    }

    #[Test]
    public function count_activos_retorna_cero_si_no_hay_programas_activos()
    {
        ComplementarioOfertado::factory()->count(3)->sinOferta()->create();

        $count = $this->repository->countActivos();

        $this->assertEquals(0, $count);
    }

    #[Test]
    public function puede_obtener_todos_los_programas_sin_relaciones()
    {
        ComplementarioOfertado::factory()->count(4)->create();

        $programas = $this->repository->getAll();

        $this->assertCount(4, $programas);
    }
}
