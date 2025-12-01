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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class AspiranteManagementServiceTest extends TestCase
{
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
        
        $programas = new Collection([$programa1, $programa2]);

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
        
        $aspirantes = new Collection([
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
            ->with(1, ['modalidad.parametro', 'jornada', 'diasFormacion'])
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findByPrograma')
            ->once()
            ->with(1, ['persona', 'complementario'])
            ->andReturn($aspirantes);

        // No mockeamos SofiaValidationProgress - usamos la base de datos real
        // Como no hay registros en la BD de prueba, first() retornará null automáticamente

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
        
        $aspirantes = new Collection([
            new AspiranteComplementario(['id' => 1]),
            new AspiranteComplementario(['id' => 2]),
            new AspiranteComplementario(['id' => 3]),
            new AspiranteComplementario(['id' => 4]),
        ]);

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1, ['modalidad.parametro', 'jornada', 'diasFormacion'])
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findByPrograma')
            ->once()
            ->with(1, ['persona', 'complementario'])
            ->andReturn($aspirantes);

        // No mockeamos SofiaValidationProgress - usamos la base de datos real
        // Como no hay registros en la BD de prueba, first() retornará null automáticamente

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
            ->twice()
            ->with(self::TEST_NUMERO_DOCUMENTO)
            ->andReturn($persona);

        $this->aspiranteRepositoryMock->shouldReceive('existeInscripcion')
            ->once()
            ->with(1, 1)
            ->andReturn(false);

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

        $this->aspiranteRepositoryMock->shouldReceive('existeInscripcion')
            ->once()
            ->with(1, 1)
            ->andReturn(true);

        $resultado = $this->service->agregarAspirante(1, self::TEST_NUMERO_DOCUMENTO);

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('ya se encuentra inscrita', $resultado['message']);
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

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionCode(404);

        $this->service->obtenerAspirantesPorPrograma('Programa-Inexistente');
    }

    #[Test]
    public function no_obtiene_aspirantes_por_programa_id_si_no_existe(): void
    {
        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(999, ['modalidad.parametro', 'jornada', 'diasFormacion'])
            ->andReturn(null);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionCode(404);

        $this->service->obtenerAspirantesPorProgramaId(999);
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
        $persona->setAttribute('id', 1);
        $persona->setAttribute('primer_nombre', 'Juan');
        $persona->setAttribute('primer_apellido', 'Pérez');
        $persona->setAttribute('numero_documento', self::TEST_NUMERO_DOCUMENTO);

        $aspirante = new AspiranteComplementario(['id' => 1, 'persona_id' => 1, 'complementario_id' => 1]);
        $aspirante->setRelation('persona', $persona);

        $collection = new Collection([$aspirante]);

        \Illuminate\Support\Facades\Auth::shouldReceive('user')
            ->andReturn(new class {
                public function can($permission) {
                    // Parameter required by Laravel's Authorizable interface signature
                    unset($permission);
                    return true;
                }
            });

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findByPrograma')
            ->twice()
            ->with(1)
            ->andReturn($collection);

        $this->aspiranteRepositoryMock->shouldReceive('update')
            ->once()
            ->with($aspirante, ['estado' => 4])
            ->andReturn(true);

        $resultado = $this->service->rechazarAspirante(1, 1);

        $this->assertTrue($resultado['success']);
        $this->assertStringContainsString('Juan', $resultado['message']);
        $this->assertStringContainsString(self::TEST_NUMERO_DOCUMENTO, $resultado['message']);
    }

    #[Test]
    public function no_rechaza_aspirante_sin_permisos(): void
    {
        \Illuminate\Support\Facades\Auth::shouldReceive('user')
            ->andReturn(new class {
                public function can($permission) {
                    // Parameter required by Laravel's Authorizable interface signature
                    unset($permission);
                    return false;
                }
            });

        $resultado = $this->service->rechazarAspirante(1, 1);

        $this->assertFalse($resultado['success']);
        $this->assertEquals(403, $resultado['status_code']);
        $this->assertStringContainsString('permisos', $resultado['message']);
    }

    #[Test]
    public function no_rechaza_aspirante_si_programa_no_existe(): void
    {
        \Illuminate\Support\Facades\Auth::shouldReceive('user')
            ->andReturn(new class {
                public function can($permission) {
                    // Parameter required by Laravel's Authorizable interface signature
                    unset($permission);
                    return true;
                }
            });

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

        \Illuminate\Support\Facades\Auth::shouldReceive('user')
            ->andReturn(new class {
                public function can($permission) {
                    // Parameter required by Laravel's Authorizable interface signature
                    unset($permission);
                    return true;
                }
            });

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andReturn($programa);

        $this->aspiranteRepositoryMock->shouldReceive('findByPrograma')
            ->once()
            ->with(1)
            ->andReturn(new Collection([]));

        $resultado = $this->service->rechazarAspirante(1, 999);

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString('Aspirante no encontrado', $resultado['message']);
    }

    #[Test]
    public function maneja_excepcion_al_rechazar_aspirante(): void
    {
        \Illuminate\Support\Facades\Auth::shouldReceive('user')
            ->andReturn(new class {
                public function can($permission) {
                    // Parameter required by Laravel's Authorizable interface signature
                    unset($permission);
                    return true;
                }
            });

        $this->programaRepositoryMock->shouldReceive('findWithRelations')
            ->once()
            ->with(1)
            ->andThrow(new \Exception('Error de base de datos'));

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

        $aspirantes = new Collection([$aspirante]);
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
            ->andReturn(new Collection([]));

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

        \Illuminate\Support\Facades\Auth::shouldReceive('id')
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
