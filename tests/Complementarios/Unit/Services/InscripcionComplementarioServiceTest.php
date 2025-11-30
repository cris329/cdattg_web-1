<?php

namespace Tests\Complementarios\Unit\Services;

use Tests\TestCase;
use App\Services\Complementarios\InscripcionComplementarioService;
use App\Repositories\PersonaRepository;
use App\Repositories\Complementarios\AspiranteComplementarioRepository;
use App\Repositories\Complementarios\ComplementarioOfertadoRepository;
use App\Repositories\TemaRepository;
use App\Services\Complementarios\ComplementarioService;
use App\Services\UserService;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Persona;
use App\Models\Pais;
use App\Models\Departamento;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class InscripcionComplementarioServiceTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_NUMERO_DOCUMENTO = '1234567890';
    private const TEST_APELLIDO = 'Pérez';
    private const TEST_FECHA_NACIMIENTO = '1990-01-01';
    private const TEST_CELULAR = '3001234567';
    private const TEST_EMAIL = 'juan@test.com';
    private const TEST_DIRECCION = 'Calle 123';

    protected InscripcionComplementarioService $service;
    protected $personaRepositoryMock;
    protected $aspiranteRepositoryMock;
    protected $programaRepositoryMock;
    protected $temaRepositoryMock;
    protected $complementarioServiceMock;
    protected $userServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->personaRepositoryMock = Mockery::mock(PersonaRepository::class);
        $this->aspiranteRepositoryMock = Mockery::mock(AspiranteComplementarioRepository::class);
        $this->programaRepositoryMock = Mockery::mock(ComplementarioOfertadoRepository::class);
        $this->temaRepositoryMock = Mockery::mock(TemaRepository::class);
        $this->complementarioServiceMock = Mockery::mock(ComplementarioService::class);
        $this->userServiceMock = Mockery::mock(UserService::class);
        
        $this->service = new InscripcionComplementarioService(
            $this->personaRepositoryMock,
            $this->aspiranteRepositoryMock,
            $this->programaRepositoryMock,
            $this->temaRepositoryMock,
            $this->complementarioServiceMock,
            $this->userServiceMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function puede_preparar_formulario_general()
    {
        // NOTA: Este test requiere BD porque el servicio usa directamente Pais::all() y Departamento::all()
        // que son difíciles de mockear sin cambiar el código del servicio.
        // Mockeamos lo que es posible (repositorios y servicios inyectados)
        
        // Ejecutar seeders necesarios para evitar conflictos de restricción única
        $this->seed([
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
        ]);
        
        $this->temaRepositoryMock->shouldReceive('obtenerCaracterizacionesComplementarias')
            ->once()
            ->andReturn(null);

        $this->complementarioServiceMock->shouldReceive('getTiposDocumento')
            ->once()
            ->andReturn(new EloquentCollection([]));

        $this->complementarioServiceMock->shouldReceive('getGeneros')
            ->once()
            ->andReturn(new EloquentCollection([]));

        $data = $this->service->prepararFormularioGeneral();

        $this->assertArrayHasKey('paises', $data);
        $this->assertArrayHasKey('departamentos', $data);
        $this->assertArrayHasKey('tiposDocumento', $data);
        $this->assertArrayHasKey('generos', $data);
    }

    /** @test */
    public function puede_procesar_inscripcion_general()
    {
        $data = [
            'tipo_documento' => 1,
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
            'primer_nombre' => 'Juan',
            'primer_apellido' => self::TEST_APELLIDO,
            'fecha_nacimiento' => self::TEST_FECHA_NACIMIENTO,
            'genero' => 1,
            'celular' => self::TEST_CELULAR,
            'email' => self::TEST_EMAIL,
            'pais_id' => 1,
            'departamento_id' => 1,
            'municipio_id' => 1,
            'direccion' => self::TEST_DIRECCION,
        ];

        $persona = new Persona();
        $persona->id = 1;
        $persona->numero_documento = self::TEST_NUMERO_DOCUMENTO;
        $persona->email = self::TEST_EMAIL;

        $this->personaRepositoryMock->shouldReceive('existsByDocumentoOrEmail')
            ->once()
            ->with(self::TEST_NUMERO_DOCUMENTO, self::TEST_EMAIL)
            ->andReturn(false);

        $this->personaRepositoryMock->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($persona);

        $response = $this->service->procesarInscripcionGeneral($data);

        $this->assertTrue($response->isRedirect());
        $this->assertTrue($response->getSession()->has('success'));
    }

    /** @test */
    public function no_procesa_inscripcion_general_si_persona_ya_existe()
    {
        $data = [
            'tipo_documento' => 1,
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
            'email' => self::TEST_EMAIL,
            'primer_nombre' => 'Juan',
            'primer_apellido' => self::TEST_APELLIDO,
            'fecha_nacimiento' => self::TEST_FECHA_NACIMIENTO,
            'genero' => 1,
            'celular' => self::TEST_CELULAR,
            'pais_id' => 1,
            'departamento_id' => 1,
            'municipio_id' => 1,
            'direccion' => self::TEST_DIRECCION,
        ];

        $this->personaRepositoryMock->shouldReceive('existsByDocumentoOrEmail')
            ->once()
            ->with(self::TEST_NUMERO_DOCUMENTO, self::TEST_EMAIL)
            ->andReturn(true);

        $response = $this->service->procesarInscripcionGeneral($data);

        $this->assertTrue($response->isRedirect());
        $this->assertTrue($response->getSession()->has('error'));
    }

    /** @test */
    public function puede_preparar_formulario_inscripcion()
    {
        $programa = new ComplementarioOfertado();
        $programa->id = 1;
        $programa->nombre = 'Programa Test';

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1, ['modalidad.parametro', 'jornada'])
            ->andReturn($programa);

        $this->temaRepositoryMock->shouldReceive('obtenerTiposDocumento')
            ->once()
            ->andReturn(null);

        $this->temaRepositoryMock->shouldReceive('obtenerGeneros')
            ->once()
            ->andReturn(null);

        $this->temaRepositoryMock->shouldReceive('obtenerCaracterizacionesComplementarias')
            ->once()
            ->andReturn(null);

        $this->temaRepositoryMock->shouldReceive('obtenerVias')
            ->once()
            ->andReturn(null);

        $this->temaRepositoryMock->shouldReceive('obtenerLetras')
            ->once()
            ->andReturn(null);

        $this->temaRepositoryMock->shouldReceive('obtenerCardinales')
            ->once()
            ->andReturn(null);

        $this->complementarioServiceMock->shouldReceive('getTiposDocumento')
            ->once()
            ->andReturn(new EloquentCollection([]));

        $this->complementarioServiceMock->shouldReceive('getGeneros')
            ->once()
            ->andReturn(new EloquentCollection([]));

        Auth::shouldReceive('check')
            ->once()
            ->andReturn(false);

        // Ejecutar seeders necesarios para evitar conflictos de restricción única
        $this->seed([
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
        ]);

        $data = $this->service->prepararFormularioInscripcion(1);

        $this->assertArrayHasKey('programa', $data);
        $this->assertEquals(1, $data['programa']->id);
        $this->assertArrayHasKey('paises', $data);
        $this->assertArrayHasKey('departamentos', $data);
    }

    /** @test */
    public function lanza_excepcion_si_programa_no_existe()
    {
        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(99999, ['modalidad.parametro', 'jornada'])
            ->andReturn(null);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $this->service->prepararFormularioInscripcion(99999);
    }

    /** @test */
    public function puede_procesar_inscripcion_a_programa()
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

        // Obtener datos del seeder (ya existen por los seeders ejecutados)
        $pais = Pais::first();
        $departamento = Departamento::where('pais_id', $pais->id)->first();
        $municipio = \App\Models\Municipio::where('departamento_id', $departamento->id)->first();
        
        $programa = ComplementarioOfertado::factory()->conOferta()->create();

        // Asegurar que existen los parametros_temas necesarios en la base de datos
        $parametroDoc = \App\Models\Parametro::firstOrCreate(
            ['id' => 1],
            ['name' => 'CÉDULA DE CIUDADANÍA', 'status' => 1]
        );
        $temaDoc = \App\Models\Tema::firstOrCreate(
            ['id' => 2],
            ['name' => 'TIPO DE DOCUMENTO', 'status' => 1]
        );
        \App\Models\ParametroTema::firstOrCreate(
            [
                'parametro_id' => $parametroDoc->id,
                'tema_id' => $temaDoc->id,
            ],
            ['status' => 1]
        );

        $parametroGenero = \App\Models\Parametro::firstOrCreate(
            ['id' => 9],
            ['name' => 'MASCULINO', 'status' => 1]
        );
        $temaGenero = \App\Models\Tema::firstOrCreate(
            ['id' => 3],
            ['name' => 'GÉNERO', 'status' => 1]
        );
        \App\Models\ParametroTema::firstOrCreate(
            [
                'parametro_id' => $parametroGenero->id,
                'tema_id' => $temaGenero->id,
            ],
            ['status' => 1]
        );

        // Crear servicio real sin mocks para este test
        $userService = new UserService();
        $complementarioService = new ComplementarioService(
            Mockery::mock(TemaRepository::class),
            new ComplementarioOfertadoRepository(),
            new AspiranteComplementarioRepository()
        );

        $service = new InscripcionComplementarioService(
            new PersonaRepository(),
            new AspiranteComplementarioRepository(),
            new ComplementarioOfertadoRepository(),
            Mockery::mock(TemaRepository::class),
            $complementarioService,
            $userService
        );

        $data = [
            'tipo_documento' => 1, // parametro_id
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
            'primer_nombre' => 'Juan',
            'primer_apellido' => self::TEST_APELLIDO,
            'fecha_nacimiento' => self::TEST_FECHA_NACIMIENTO,
            'genero' => 9, // parametro_id
            'celular' => self::TEST_CELULAR,
            'email' => self::TEST_EMAIL,
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => self::TEST_DIRECCION,
            'acepto_privacidad' => '1',
            'acepto_terminos' => '1',
        ];

        $response = $service->procesarInscripcion($data, $programa->id);

        // Validar que se creó la persona
        $this->assertDatabaseHas('personas', [
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
            'email' => self::TEST_EMAIL,
        ]);

        // Validar que se creó el aspirante
        $this->assertDatabaseHas('aspirantes_complementarios', [
            'complementario_id' => $programa->id,
        ]);

        // Validar que se creó el usuario
        $persona = Persona::where('numero_documento', self::TEST_NUMERO_DOCUMENTO)->first();
        $this->assertDatabaseHas('users', [
            'email' => self::TEST_EMAIL,
            'persona_id' => $persona->id,
        ]);

        // Validar redirect
        $this->assertTrue($response->isRedirect(route('login.index')));
        $this->assertTrue($response->getSession()->has('success'));
    }

    /** @test */
    public function no_procesa_inscripcion_si_usuario_ya_esta_inscrito()
    {
        $programaId = 1;
        $personaId = 1;

        $user = Mockery::mock(\App\Models\User::class)->makePartial();
        $user->persona_id = $personaId;
        $user->shouldAllowMockingProtectedMethods();

        Auth::shouldReceive('check')
            ->once()
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $this->aspiranteRepositoryMock->shouldReceive('existeInscripcion')
            ->once()
            ->with($personaId, $programaId)
            ->andReturn(true);

        $data = [
            'tipo_documento' => 1,
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
            'primer_nombre' => 'Juan',
            'primer_apellido' => self::TEST_APELLIDO,
            'fecha_nacimiento' => self::TEST_FECHA_NACIMIENTO,
            'genero' => 1,
            'celular' => self::TEST_CELULAR,
            'email' => self::TEST_EMAIL,
            'pais_id' => 1,
            'departamento_id' => 1,
            'municipio_id' => 1,
            'direccion' => self::TEST_DIRECCION,
        ];

        $response = $this->service->procesarInscripcion($data, $programaId);

        $this->assertTrue($response->isRedirect());
        $this->assertTrue($response->getSession()->has('error'));
    }
}
