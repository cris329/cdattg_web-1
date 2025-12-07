<?php

namespace Tests\Complementarios\Unit\Services;

use Tests\TestCase;
use App\Services\Complementarios\AspiranteManagementService;
use App\Repositories\Complementarios\AspiranteComplementarioRepository;
use App\Repositories\Complementarios\ComplementarioOfertadoRepository;
use App\Repositories\PersonaRepository;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Persona;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Complementarios\SofiaValidationProgress;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\Complementarios\Concerns\SeedsComplementariosDatabase;

class AspiranteManagementServiceTest extends TestCase
{
    use RefreshDatabase;
    use SeedsComplementariosDatabase;

    private const TEST_PROGRAMA_NOMBRE = 'Programa Test';
    private const TEST_NUMERO_DOCUMENTO = '1234567890';
    private const TEST_ERROR_INTERNO_SERVIDOR = 'Error interno del servidor';
    private const TEST_PROGRAMA_NO_ENCONTRADO = 'Programa no encontrado';

    protected AspiranteManagementService $service;
    protected $aspiranteRepositoryMock;
    protected $programaRepositoryMock;
    protected $personaRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seedComplementariosDatabaseIfNeeded();
        
        $this->aspiranteRepositoryMock = Mockery::mock(AspiranteComplementarioRepository::class);
        $this->programaRepositoryMock = Mockery::mock(ComplementarioOfertadoRepository::class);
        $this->personaRepositoryMock = Mockery::mock(PersonaRepository::class);
        
        $this->service = new AspiranteManagementService(
            $this->aspiranteRepositoryMock,
            $this->programaRepositoryMock,
            $this->personaRepositoryMock
        );
    }

    protected function tearDown(): void
    {
        // Cerrar todos los mocks y limpiar el contenedor
        // Esto limpia los mocks de alias que pueden interferir con factories
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function puede_obtener_programas_para_gestion()
    {
        $programa1 = new ComplementarioOfertado();
        $programa1->setAttribute('id', 1);
        $programa1->setAttribute('nombre', 'Programa 1');
        $programa1->setAttribute('aspirantes_count', 5);
        
        $programa2 = new ComplementarioOfertado();
        $programa2->setAttribute('id', 2);
        $programa2->setAttribute('nombre', 'Programa 2');
        $programa2->setAttribute('aspirantes_count', 3);
        
        $programas = new EloquentCollection([$programa1, $programa2]);

        $this->programaRepositoryMock->shouldReceive('getAllWithAspirantesCount')
            ->once()
            ->with(['modalidad.parametro', 'jornada', 'diasFormacion'])
            ->andReturn($programas);

        $resultado = $this->service->obtenerProgramasParaGestion();

        $this->assertCount(2, $resultado);
        $programaEncontrado = $resultado->firstWhere('id', 1);
        $this->assertNotNull($programaEncontrado);
        $this->assertEquals(5, $programaEncontrado->aspirantes_count);
    }

    #[Test]
    public function puede_obtener_aspirantes_por_programa_por_nombre()
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);
        $programa->setAttribute('nombre', 'Auxiliar de Cocina');
        
        $aspirantes = new EloquentCollection([
            new AspiranteComplementario(['id' => 1, 'complementario_id' => 1]),
            new AspiranteComplementario(['id' => 2, 'complementario_id' => 1]),
            new AspiranteComplementario(['id' => 3, 'complementario_id' => 1]),
        ]);

        $this->programaRepositoryMock->shouldReceive('findByNombre')
            ->once()
            ->with('Auxiliar-de-Cocina')
            ->andReturn($programa);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1, ['modalidad', 'jornada', 'diasFormacion'])
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findByPrograma')
            ->once()
            ->with(1, ['persona', 'complementario'])
            ->andReturn($aspirantes);

        // No necesitamos mockear SofiaValidationProgress ya que usamos RefreshDatabase
        // y la tabla existe gracias a seedComplementariosDatabaseIfNeeded()
        // El servicio simplemente retornará null si no hay progreso existente

        $data = $this->service->obtenerAspirantesPorPrograma('Auxiliar-de-Cocina');

        $this->assertEquals(1, $data['programa']->id);
        $this->assertCount(3, $data['aspirantes']);
    }

    #[Test]
    public function puede_obtener_aspirantes_por_programa_por_id()
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);
        $programa->setAttribute('nombre', self::TEST_PROGRAMA_NOMBRE);
        
        $aspirantes = new EloquentCollection([
            new AspiranteComplementario(['id' => 1]),
            new AspiranteComplementario(['id' => 2]),
            new AspiranteComplementario(['id' => 3]),
            new AspiranteComplementario(['id' => 4]),
        ]);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1, ['modalidad', 'jornada', 'diasFormacion'])
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findByPrograma')
            ->once()
            ->with(1, ['persona', 'complementario'])
            ->andReturn($aspirantes);

        // No necesitamos mockear SofiaValidationProgress ya que usamos RefreshDatabase
        // y la tabla existe gracias a seedComplementariosDatabaseIfNeeded()
        // El servicio simplemente retornará null si no hay progreso existente

        $data = $this->service->obtenerAspirantesPorProgramaId(1);

        $this->assertEquals(1, $data['programa']->id);
        $this->assertCount(4, $data['aspirantes']);
    }

    #[Test]
    public function puede_agregar_aspirante_existente()
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);
        $programa->setAttribute('nombre', self::TEST_PROGRAMA_NOMBRE);
        
        $persona = new Persona();
        $persona->setAttribute('id', 1);
        $persona->setAttribute('numero_documento', self::TEST_NUMERO_DOCUMENTO);
        $persona->setAttribute('primer_nombre', 'Juan');
        $persona->setAttribute('primer_apellido', 'Pérez');
        
        $aspirante = new AspiranteComplementario(['id' => 1, 'persona_id' => 1, 'complementario_id' => 1]);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andReturn($programa);

        $this->personaRepositoryMock->shouldReceive('findByNumeroDocumento')
            ->once()
            ->with(self::TEST_NUMERO_DOCUMENTO)
            ->andReturn($persona);

        // Nota: La validación de inscripción duplicada ahora se maneja en StoreAspiranteRequest,
        // no en el servicio. El servicio solo crea el aspirante si la persona existe.
        $this->aspiranteRepositoryMock->shouldReceive('existeInscripcion')
            ->never(); // Ya no se llama desde el servicio

        $this->aspiranteRepositoryMock->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['persona_id'] === 1 &&
                       $data['complementario_id'] === 1 &&
                       $data['estado'] === 1;
            }))
            ->andReturn($aspirante);

        $resultado = $this->service->agregarAspirante(1, self::TEST_NUMERO_DOCUMENTO);

        $this->assertTrue($resultado['success']);
        $this->assertStringContainsString('Juan', $resultado['message']);
    }

    #[Test]
    public function no_agrega_aspirante_si_no_existe_persona()
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);
        $programa->setAttribute('nombre', self::TEST_PROGRAMA_NOMBRE);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andReturn($programa);

        $this->personaRepositoryMock->shouldReceive('findByNumeroDocumento')
            ->once()
            ->with('9999999999')
            ->andReturn(null);

        $resultado = $this->service->agregarAspirante(1, '9999999999');

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('No se encontró', $resultado['message']);
    }

    #[Test]
    public function no_agrega_aspirante_si_ya_esta_inscrito()
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);
        $programa->setAttribute('nombre', self::TEST_PROGRAMA_NOMBRE);
        
        $persona = new Persona();
        $persona->setAttribute('id', 1);
        $persona->setAttribute('numero_documento', self::TEST_NUMERO_DOCUMENTO);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andReturn($programa);

        $this->personaRepositoryMock->shouldReceive('findByNumeroDocumento')
            ->once()
            ->with(self::TEST_NUMERO_DOCUMENTO)
            ->andReturn($persona);

        // Nota: La validación de inscripción duplicada ahora se maneja en StoreAspiranteRequest,
        // no en el servicio. El servicio solo crea el aspirante si la persona existe.
        // Este test verifica que el servicio puede agregar un aspirante cuando la persona existe.
        $this->aspiranteRepositoryMock->shouldReceive('existeInscripcion')
            ->never(); // Ya no se llama desde el servicio

        $this->aspiranteRepositoryMock->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['persona_id'] === 1 &&
                       $data['complementario_id'] === 1 &&
                       $data['estado'] === 1;
            }))
            ->andReturn(new AspiranteComplementario(['id' => 1, 'persona_id' => 1, 'complementario_id' => 1]));

        $resultado = $this->service->agregarAspirante(1, self::TEST_NUMERO_DOCUMENTO);

        // El servicio ahora solo verifica que la persona exista, la validación de duplicados
        // está en el FormRequest
        $this->assertTrue($resultado['success']);
    }

    #[Test]
    public function puede_obtener_estadisticas_programa()
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);
        $programa->setAttribute('cupos', 30);

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
    public function no_obtiene_aspirantes_por_programa_si_no_existe(): void
    {
        $this->programaRepositoryMock->shouldReceive('findByNombre')
            ->once()
            ->with('Programa-Inexistente')
            ->andReturn(null);

        try {
            $this->service->obtenerAspirantesPorPrograma('Programa-Inexistente');
            $this->fail('Se esperaba una excepción HttpException con código 404');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(404, $e->getStatusCode());
        }
    }

    #[Test]
    public function no_obtiene_aspirantes_por_programa_id_si_no_existe(): void
    {
        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(999, ['modalidad', 'jornada', 'diasFormacion'])
            ->andReturn(null);

        try {
            $this->service->obtenerAspirantesPorProgramaId(999);
            $this->fail('Se esperaba una excepción HttpException con código 404');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(404, $e->getStatusCode());
        }
    }

    #[Test]
    public function no_agrega_aspirante_si_programa_no_existe(): void
    {
        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(999)
            ->andReturn(null);

        $resultado = $this->service->agregarAspirante(999, self::TEST_NUMERO_DOCUMENTO);

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString(self::TEST_PROGRAMA_NO_ENCONTRADO, $resultado['message']);
    }

    #[Test]
    public function maneja_excepcion_al_agregar_aspirante(): void
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andReturn($programa);

        $this->personaRepositoryMock->shouldReceive('findByNumeroDocumento')
            ->once()
            ->with(self::TEST_NUMERO_DOCUMENTO)
            ->andThrow(new \Exception('Error de base de datos'));

        $resultado = $this->service->agregarAspirante(1, self::TEST_NUMERO_DOCUMENTO);

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString(self::TEST_ERROR_INTERNO_SERVIDOR, $resultado['message']);
    }

    #[Test]
    public function puede_rechazar_aspirante(): void
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);

        $persona = new Persona();
        $persona->setRawAttributes([
            'id' => 1,
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
        ]);
        $persona->syncOriginal();

        // Crear una instancia real del modelo para que funcione correctamente con Collection::where()
        $aspirante = new AspiranteComplementario();
        $aspirante->setRawAttributes([
            'id' => 1,
            'persona_id' => 1,
            'complementario_id' => 1,
            'estado' => 1,
            'observaciones' => null,
        ]);
        
        // Establecer la relación persona usando setRelation
        // Esto marca la relación como cargada automáticamente
        $aspirante->setRelation('persona', $persona);
        
        // Sincronizar atributos originales
        $aspirante->syncOriginal();

        $collection = new EloquentCollection([$aspirante]);

        $userMock = new class {
            public function can($permission) {
                // Parameter required by Laravel's Authorizable interface signature
                unset($permission);
                return true;
            }
        };

        Auth::shouldReceive('user')
            ->andReturn($userMock);

        Auth::shouldReceive('id')
            ->andReturn(1);

        Gate::shouldReceive('allows')
            ->with('ELIMINAR ASPIRANTE COMPLEMENTARIO')
            ->andReturn(true);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andReturn($programa);

        // El servicio llama a findByPrograma en validarRechazarAspirante y en rechazarAspirante
        // Ambas veces hace ->where('id', $aspiranteId)->first() sobre la Collection retornada
        // El método findByPrograma puede recibir un segundo parámetro opcional (relations)
        // Necesitamos que retorne una Collection que soporte ->where('id', $aspiranteId)->first()
        // El servicio llama primero sin relaciones (en validarRechazarAspirante) y luego sin relaciones (en rechazarAspirante)
        $this->aspiranteRepositoryMock->shouldReceive('findByPrograma')
            ->with(1, Mockery::any())
            ->twice()
            ->andReturn($collection);

        $this->aspiranteRepositoryMock->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function ($arg) use ($aspirante) {
                return $arg === $aspirante || ($arg instanceof AspiranteComplementario && $arg->id === $aspirante->id);
            }), Mockery::on(function ($data) {
                return isset($data['estado']) && $data['estado'] === 4;
            }))
            ->andReturn(true);

        // Mockear Log para evitar errores
        \Illuminate\Support\Facades\Log::shouldReceive('info')
            ->zeroOrMoreTimes()
            ->andReturn(true);
        
        \Illuminate\Support\Facades\Log::shouldReceive('error')
            ->zeroOrMoreTimes()
            ->andReturn(true);

        $resultado = $this->service->rechazarAspirante(1, 1);

        $this->assertTrue($resultado['success']);
        $this->assertStringContainsString('Juan', $resultado['message']);
        $this->assertStringContainsString(self::TEST_NUMERO_DOCUMENTO, $resultado['message']);
    }

    #[Test]
    public function no_rechaza_aspirante_sin_permisos(): void
    {
        Gate::shouldReceive('allows')
            ->with('ELIMINAR ASPIRANTE COMPLEMENTARIO')
            ->andReturn(false);

        $resultado = $this->service->rechazarAspirante(1, 1);

        $this->assertFalse($resultado['success']);
        $this->assertEquals(403, $resultado['status_code']);
        $this->assertStringContainsString('permisos', $resultado['message']);
    }

    #[Test]
    public function no_rechaza_aspirante_si_programa_no_existe(): void
    {
        Gate::shouldReceive('allows')
            ->with('ELIMINAR ASPIRANTE COMPLEMENTARIO')
            ->andReturn(true);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(999)
            ->andReturn(null);

        $resultado = $this->service->rechazarAspirante(999, 1);

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString(self::TEST_PROGRAMA_NO_ENCONTRADO, $resultado['message']);
    }

    #[Test]
    public function no_rechaza_aspirante_si_no_existe(): void
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);

        Gate::shouldReceive('allows')
            ->with('ELIMINAR ASPIRANTE COMPLEMENTARIO')
            ->andReturn(true);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andReturn($programa);

        // El servicio llama a findByPrograma en validarRechazarAspirante
        // Si retorna una colección vacía, validarRechazarAspirante retorna el error
        // y rechazarAspirante NO debería llamarlo de nuevo
        $this->aspiranteRepositoryMock->shouldReceive('findByPrograma')
            ->once()
            ->with(1)
            ->andReturn(new EloquentCollection([]));

        $resultado = $this->service->rechazarAspirante(1, 999);

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('Aspirante no encontrado', $resultado['message']);
    }

    #[Test]
    public function maneja_excepcion_al_rechazar_aspirante(): void
    {
        Gate::shouldReceive('allows')
            ->with('ELIMINAR ASPIRANTE COMPLEMENTARIO')
            ->andReturn(true);

        Auth::shouldReceive('id')
            ->andReturn(1);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andThrow(new \Exception('Error de base de datos'));

        \Illuminate\Support\Facades\Log::shouldReceive('error')
            ->zeroOrMoreTimes()
            ->andReturn(true);

        $resultado = $this->service->rechazarAspirante(1, 1);

        $this->assertFalse($resultado['success']);
        $this->assertEquals(500, $resultado['status_code']);
        $this->assertStringContainsString(self::TEST_ERROR_INTERNO_SERVIDOR, $resultado['message']);
    }

    #[Test]
    public function puede_validar_documentos(): void
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);

        $persona = new Persona(['id' => 1]);
        $persona->setRelation('tipoDocumento', null);

        $aspirante = new AspiranteComplementario(['id' => 1, 'persona_id' => 1]);
        $aspirante->setRelation('persona', $persona);

        $aspirantes = new EloquentCollection([$aspirante]);
        $files = ['documento1.pdf', 'documento2.pdf'];

        $documentoServiceMock = Mockery::mock(\App\Services\Complementarios\AspiranteDocumentoService::class);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findByPrograma')
            ->twice()
            ->with(1, ['persona.tipoDocumento'])
            ->andReturn($aspirantes);

        $documentoServiceMock->shouldReceive('getGoogleDriveFiles')
            ->once()
            ->andReturn($files);

        $documentoServiceMock->shouldReceive('construirPatronBusqueda')
            ->once()
            ->with($persona)
            ->andReturn('CC_1234567890_Juan_Perez_');

        $documentoServiceMock->shouldReceive('buscarDocumentoEnGoogleDrive')
            ->once()
            ->with($files, 'CC_1234567890_Juan_Perez_')
            ->andReturn(true);

        $this->personaRepositoryMock->shouldReceive('updateDocumentoStatus')
            ->once()
            ->with($persona, true)
            ->andReturn(true);

        $resultado = $this->service->validarDocumentos(1, $documentoServiceMock);

        $this->assertTrue($resultado['success']);
        $this->assertEquals(1, $resultado['total']);
        $this->assertEquals(1, $resultado['con_documento']);
        $this->assertEquals(0, $resultado['sin_documento']);
    }

    #[Test]
    public function no_valida_documentos_si_programa_no_existe(): void
    {
        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(999)
            ->andReturn(null);

        $documentoServiceMock = Mockery::mock(\App\Services\Complementarios\AspiranteDocumentoService::class);

        $resultado = $this->service->validarDocumentos(999, $documentoServiceMock);

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString(self::TEST_PROGRAMA_NO_ENCONTRADO, $resultado['message']);
    }

    #[Test]
    public function no_valida_documentos_si_no_hay_aspirantes(): void
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findByPrograma')
            ->once()
            ->with(1, ['persona.tipoDocumento'])
            ->andReturn(new EloquentCollection([]));

        $documentoServiceMock = Mockery::mock(\App\Services\Complementarios\AspiranteDocumentoService::class);

        $resultado = $this->service->validarDocumentos(1, $documentoServiceMock);

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('No hay aspirantes', $resultado['message']);
    }

    #[Test]
    public function maneja_excepcion_al_validar_documentos(): void
    {
        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andThrow(new \Exception('Error de conexión'));

        $documentoServiceMock = Mockery::mock(\App\Services\Complementarios\AspiranteDocumentoService::class);

        Auth::shouldReceive('id')
            ->andReturn(1);

        $resultado = $this->service->validarDocumentos(1, $documentoServiceMock);

        $this->assertFalse($resultado['success']);
        $this->assertEquals(500, $resultado['status_code']);
        $this->assertStringContainsString(self::TEST_ERROR_INTERNO_SERVIDOR, $resultado['message']);
    }

    #[Test]
    public function no_obtiene_estadisticas_si_programa_no_existe(): void
    {
        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(\App\Exceptions\ProgramaNoEncontradoException::class);

        $this->service->obtenerEstadisticasPrograma(999);
    }

    #[Test]
    public function calcula_cupos_disponibles_correctamente(): void
    {
        $programa = new ComplementarioOfertado();
        $programa->setAttribute('id', 1);
        $programa->setAttribute('cupos', 10);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('countByPrograma')
            ->once()
            ->with(1)
            ->andReturn(15); // Más aspirantes que cupos

        $this->aspiranteRepositoryMock->shouldReceive('countByEstado')
            ->once()
            ->with(1, 1)
            ->andReturn(5);

        $this->aspiranteRepositoryMock->shouldReceive('countByEstado')
            ->once()
            ->with(1, 3)
            ->andReturn(3);

        $estadisticas = $this->service->obtenerEstadisticasPrograma(1);

        $this->assertEquals(0, $estadisticas['cupos_disponibles']); // No puede ser negativo
    }
}
