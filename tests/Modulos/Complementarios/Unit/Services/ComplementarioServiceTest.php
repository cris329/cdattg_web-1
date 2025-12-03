<?php

namespace Tests\Complementarios\Unit\Services;

use Tests\TestCase;
use App\Services\Complementarios\ComplementarioService;
use App\Repositories\TemaRepository;
use App\Repositories\Complementarios\ComplementarioOfertadoRepository;
use App\Repositories\Complementarios\AspiranteComplementarioRepository;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\ParametroTema;
use App\Models\JornadaFormacion;
use App\Models\Ambiente;
use App\Models\Parametro;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class ComplementarioServiceTest extends TestCase
{
    use RefreshDatabase;
    private const TEST_OBSERVACIONES = 'Observaciones test';
    private const TEST_HORA_INICIO = '08:00:00';
    private const TEST_HORA_FIN = '12:00:00';

    protected ComplementarioService $service;
    protected $temaRepositoryMock;
    protected $programaRepositoryMock;
    protected $aspiranteRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->temaRepositoryMock = Mockery::mock(TemaRepository::class);
        $this->programaRepositoryMock = Mockery::mock(ComplementarioOfertadoRepository::class);
        $this->aspiranteRepositoryMock = Mockery::mock(AspiranteComplementarioRepository::class);
        
        $this->service = new ComplementarioService(
            $this->temaRepositoryMock,
            $this->programaRepositoryMock,
            $this->aspiranteRepositoryMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function puede_obtener_icono_para_programa()
    {
        $icono = $this->service->getIconoForPrograma('Auxiliar de Cocina');
        
        $this->assertEquals('fas fa-utensils', $icono);
    }

    #[Test]
    public function retorna_icono_por_defecto_si_no_existe()
    {
        $icono = $this->service->getIconoForPrograma('Programa Desconocido');
        
        $this->assertEquals('fas fa-graduation-cap', $icono);
    }

    #[Test]
    public function puede_obtener_clase_badge_por_estado()
    {
        $clase0 = $this->service->getBadgeClassForEstado(0);
        $clase1 = $this->service->getBadgeClassForEstado(1);
        $clase2 = $this->service->getBadgeClassForEstado(2);
        
        $this->assertEquals('bg-secondary', $clase0);
        $this->assertEquals('bg-success', $clase1);
        $this->assertEquals('bg-warning', $clase2);
    }

    #[Test]
    public function puede_obtener_label_estado()
    {
        $label0 = $this->service->getEstadoLabel(0);
        $label1 = $this->service->getEstadoLabel(1);
        $label2 = $this->service->getEstadoLabel(2);
        
        $this->assertEquals('Sin Oferta', $label0);
        $this->assertEquals('Con Oferta', $label1);
        $this->assertEquals('Cupos Llenos', $label2);
    }

    #[Test]
    public function puede_enriquecer_programa()
    {
        $modalidad = new ParametroTema(['id' => 1, 'tema_id' => 5]);
        $modalidad->setRelation('parametro', new Parametro(['id' => 1, 'name' => 'Presencial']));
        
        $jornada = new JornadaFormacion(['id' => 1, 'jornada' => 'Diurna']);
        
        $programa = new ComplementarioOfertado();
        $programa->id = 1;
        $programa->nombre = 'Auxiliar de Cocina';
        $programa->estado = 1;
        $programa->modalidad_id = 1;
        $programa->jornada_id = 1;
        $programa->setRelation('modalidad', $modalidad);
        $programa->setRelation('jornada', $jornada);

        // Verificar que el estado se estableció correctamente
        $this->assertEquals(1, $programa->estado);

        $enriquecido = $this->service->enriquecerPrograma($programa);

        $this->assertEquals('fas fa-utensils', $enriquecido->icono);
        // El servicio asigna badge_class directamente, pero el accessor del modelo puede interferir
        // Verificamos que el servicio haya asignado el valor correcto accediendo a los atributos
        $this->assertEquals('bg-success', $enriquecido->getAttributes()['badge_class'] ?? $enriquecido->badge_class);
        $this->assertEquals('Con Oferta', $enriquecido->estado_label);
        $this->assertEquals('Presencial', $enriquecido->modalidad_nombre);
        $this->assertEquals('Diurna', $enriquecido->jornada_nombre);
    }

    #[Test]
    public function puede_enriquecer_coleccion_programas()
    {
        $programa1 = new ComplementarioOfertado(['id' => 1, 'nombre' => 'Programa 1', 'estado' => 1]);
        $programa2 = new ComplementarioOfertado(['id' => 2, 'nombre' => 'Programa 2', 'estado' => 0]);
        $programa3 = new ComplementarioOfertado(['id' => 3, 'nombre' => 'Programa 3', 'estado' => 2]);
        $programas = new Collection([$programa1, $programa2, $programa3]);

        $this->programaRepositoryMock->shouldReceive('getAll')
            ->once()
            ->with([])
            ->andReturn($programas);

        $programasObtenidos = $this->service->obtenerProgramas();
        $enriquecidos = $this->service->enriquecerProgramas($programasObtenidos);

        $this->assertCount(3, $enriquecidos);
        $enriquecidos->each(function ($programa) {
            $this->assertNotNull($programa->icono);
            $this->assertNotNull($programa->badge_class);
            $this->assertNotNull($programa->estado_label);
        });
    }

    #[Test]
    public function puede_obtener_programas_con_filtro_estado()
    {
        $activos = new Collection([
            new ComplementarioOfertado(['id' => 1, 'estado' => 1]),
            new ComplementarioOfertado(['id' => 2, 'estado' => 1]),
            new ComplementarioOfertado(['id' => 3, 'estado' => 1]),
        ]);

        $sinOferta = new Collection([
            new ComplementarioOfertado(['id' => 4, 'estado' => 0]),
            new ComplementarioOfertado(['id' => 5, 'estado' => 0]),
        ]);

        $this->programaRepositoryMock->shouldReceive('getByEstado')
            ->once()
            ->with(1, [])
            ->andReturn($activos);

        $this->programaRepositoryMock->shouldReceive('getByEstado')
            ->once()
            ->with(0, [])
            ->andReturn($sinOferta);

        $activosObtenidos = $this->service->obtenerProgramas([], 1);
        $sinOfertaObtenidos = $this->service->obtenerProgramas([], 0);

        $this->assertCount(3, $activosObtenidos);
        $this->assertCount(2, $sinOfertaObtenidos);
    }

    #[Test]
    public function puede_verificar_inscripcion_existente()
    {
        $this->aspiranteRepositoryMock->shouldReceive('existeInscripcion')
            ->once()
            ->with(1, 1)
            ->andReturn(true);

        $this->aspiranteRepositoryMock->shouldReceive('existeInscripcion')
            ->once()
            ->with(1, 2)
            ->andReturn(false);

        $existe = $this->service->verificarInscripcionExistente(1, 1);
        $noExiste = $this->service->verificarInscripcionExistente(1, 2);

        $this->assertTrue($existe);
        $this->assertFalse($noExiste);
    }

    #[Test]
    public function puede_crear_aspirante()
    {
        $aspirante = new AspiranteComplementario([
            'id' => 1,
            'persona_id' => 1,
            'complementario_id' => 1,
            'observaciones' => self::TEST_OBSERVACIONES,
            'estado' => 1,
        ]);

        $this->aspiranteRepositoryMock->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['persona_id'] === 1 &&
                       $data['complementario_id'] === 1 &&
                       $data['observaciones'] === self::TEST_OBSERVACIONES &&
                       $data['estado'] === 1;
            }))
            ->andReturn($aspirante);

        $resultado = $this->service->crearAspirante(1, 1, self::TEST_OBSERVACIONES);

        $this->assertEquals($aspirante, $resultado);
    }

    #[Test]
    public function puede_obtener_estadisticas_programa()
    {
        $programa = new ComplementarioOfertado(['id' => 1, 'cupos' => 30]);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('countByPrograma')
            ->once()
            ->with(1)
            ->andReturn(8);

        $this->aspiranteRepositoryMock->shouldReceive('countByEstado')
            ->once()
            ->with(1, 1)
            ->andReturn(5);

        $this->aspiranteRepositoryMock->shouldReceive('countByEstado')
            ->once()
            ->with(1, 3)
            ->andReturn(3);

        $estadisticas = $this->service->obtenerEstadisticasPrograma(1);

        $this->assertEquals(8, $estadisticas['total_aspirantes']);
        $this->assertEquals(5, $estadisticas['aspirantes_activos']);
        $this->assertEquals(3, $estadisticas['aspirantes_aceptados']);
        $this->assertEquals(22, $estadisticas['cupos_disponibles']);
    }

    #[Test]
    public function puede_obtener_datos_formulario()
    {
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

        $datos = $this->service->obtenerDatosFormulario();

        $this->assertArrayHasKey('modalidades', $datos);
        $this->assertArrayHasKey('jornadas', $datos);
        $this->assertArrayHasKey('ambientes', $datos);
        $this->assertArrayHasKey('competencias', $datos);
        $this->assertArrayHasKey('guias', $datos);
    }

    #[Test]
    public function puede_sincronizar_dias_formacion()
    {
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

        $programa = ComplementarioOfertado::factory()->create();
        
        // Crear parámetros con nombres únicos para evitar violaciones de UNIQUE constraint
        $dia1 = Parametro::create(['name' => 'LUNES_TEST_' . uniqid(), 'status' => 1]);
        $dia2 = Parametro::create(['name' => 'MARTES_TEST_' . uniqid(), 'status' => 1]);

        $dias = [
            [
                'dia_id' => $dia1->id,
                'hora_inicio' => self::TEST_HORA_INICIO,
                'hora_fin' => self::TEST_HORA_FIN,
            ],
            [
                'dia_id' => $dia2->id,
                'hora_inicio' => '14:00:00',
                'hora_fin' => '18:00:00',
            ],
        ];

        $this->service->sincronizarDiasFormacion($programa, $dias);

        $programa->refresh();
        $this->assertCount(2, $programa->diasFormacion);
        $this->assertTrue($programa->diasFormacion->contains($dia1->id));
        $this->assertTrue($programa->diasFormacion->contains($dia2->id));

        $dia1Pivot = $programa->diasFormacion->firstWhere('id', $dia1->id)->pivot;
        $this->assertEquals(self::TEST_HORA_INICIO, $dia1Pivot->hora_inicio);
        $this->assertEquals(self::TEST_HORA_FIN, $dia1Pivot->hora_fin);
    }

    #[Test]
    public function puede_eliminar_dias_formacion()
    {
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

        $programa = ComplementarioOfertado::factory()->create();
        
        // Crear parámetro con nombre único para evitar violaciones de UNIQUE constraint
        $dia = Parametro::create(['name' => 'MIERCOLES_TEST_' . uniqid(), 'status' => 1]);

        $programa->diasFormacion()->attach($dia->id, [
            'hora_inicio' => self::TEST_HORA_INICIO,
            'hora_fin' => self::TEST_HORA_FIN,
        ]);

        $this->service->sincronizarDiasFormacion($programa, null);

        $programa->refresh();
        $this->assertCount(0, $programa->diasFormacion);
    }

    #[Test]
    public function puede_obtener_tipos_documento()
    {
        $parametrosCollection = collect([
            (object) ['id' => 1, 'name' => 'Cédula'],
            (object) ['id' => 2, 'name' => 'Tarjeta de Identidad'],
        ]);

        // Crear un mock del tema que extienda de Tema o sea una instancia de Tema
        $temaMock = Mockery::mock(\App\Models\Tema::class)->makePartial();
        $temaMock->id = 1;
        
        $builderMock = Mockery::mock();
        $builderMock->shouldReceive('where')
            ->once()
            ->with('parametros_temas.status', 1)
            ->andReturnSelf();
        $builderMock->shouldReceive('orderBy')
            ->once()
            ->with('parametros.name')
            ->andReturnSelf();
        $builderMock->shouldReceive('get')
            ->once()
            ->with(['parametros.id', 'parametros.name'])
            ->andReturn($parametrosCollection);

        $temaMock->shouldReceive('parametros')
            ->once()
            ->andReturn($builderMock);

        $this->temaRepositoryMock->shouldReceive('obtenerTiposDocumento')
            ->once()
            ->andReturn($temaMock);

        $tiposDocumento = $this->service->getTiposDocumento();

        $this->assertCount(2, $tiposDocumento);
        $this->assertEquals('Cédula', $tiposDocumento->first()->name);
    }

    #[Test]
    public function puede_obtener_generos()
    {
        $parametrosCollection = collect([
            (object) ['id' => 9, 'name' => 'Masculino'],
            (object) ['id' => 10, 'name' => 'Femenino'],
        ]);

        // Crear un mock del tema que extienda de Tema o sea una instancia de Tema
        $temaMock = Mockery::mock(\App\Models\Tema::class)->makePartial();
        $temaMock->id = 1;
        
        $builderMock = Mockery::mock();
        $builderMock->shouldReceive('where')
            ->once()
            ->with('parametros_temas.status', 1)
            ->andReturnSelf();
        $builderMock->shouldReceive('orderBy')
            ->once()
            ->with('parametros.name')
            ->andReturnSelf();
        $builderMock->shouldReceive('get')
            ->once()
            ->with(['parametros.id', 'parametros.name'])
            ->andReturn($parametrosCollection);

        $temaMock->shouldReceive('parametros')
            ->once()
            ->andReturn($builderMock);

        $this->temaRepositoryMock->shouldReceive('obtenerGeneros')
            ->once()
            ->andReturn($temaMock);

        $generos = $this->service->getGeneros();

        $this->assertCount(2, $generos);
        $this->assertEquals('Masculino', $generos->first()->name);
    }
}
