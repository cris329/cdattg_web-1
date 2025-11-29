<?php

namespace Tests\Complementarios\Unit\Services\Sofia;

use Tests\TestCase;
use App\Services\Sofia\SofiaValidationService;
use App\Services\Sofia\SofiaHttpClient;
use App\Services\Sofia\SofiaStateMapper;
use App\Services\AuditoriaService;
use App\Models\AspiranteComplementario;
use App\Models\Persona;
use App\Models\SofiaValidationProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Mockery;

class SofiaValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private SofiaValidationService $service;
    private $httpClientMock;
    private $stateMapperMock;
    private $auditoriaServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->httpClientMock = Mockery::mock(SofiaHttpClient::class);
        $this->stateMapperMock = Mockery::mock(SofiaStateMapper::class);
        $this->auditoriaServiceMock = Mockery::mock(AuditoriaService::class);

        $this->service = new SofiaValidationService(
            $this->httpClientMock,
            $this->stateMapperMock,
            $this->auditoriaServiceMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function puede_validar_aspirante_exitosamente(): void
    {
        $persona = Persona::factory()->create([
            'numero_documento' => '1234567890',
            'estado_sofia' => 0,
        ]);

        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
        ]);

        $this->httpClientMock->shouldReceive('validate')
            ->once()
            ->with('1234567890')
            ->andReturn('YA_EXISTE');

        $this->stateMapperMock->shouldReceive('mapToState')
            ->once()
            ->with('YA_EXISTE')
            ->andReturn(1);

        $this->stateMapperMock->shouldReceive('getStateLabel')
            ->once()
            ->with(1)
            ->andReturn('Registrado');

        $this->auditoriaServiceMock->shouldReceive('registrarValidacionSenasofiaplus')
            ->once()
            ->with(
                $aspirante->id,
                'exitoso',
                Mockery::type('string'),
                Mockery::type('array')
            );

        $resultado = $this->service->validateAspirante($aspirante, 1);

        $this->assertTrue($resultado['success']);
        $this->assertEquals('1234567890', $resultado['cedula']);
        $this->assertEquals('YA_EXISTE', $resultado['resultado']);
        $this->assertEquals(1, $resultado['estado']);
        $this->assertArrayHasKey('duration', $resultado);

        $persona->refresh();
        $this->assertEquals(1, $persona->estado_sofia);
    }

    #[Test]
    public function actualiza_estado_sofia_de_persona(): void
    {
        $persona = Persona::factory()->create([
            'numero_documento' => '1234567890',
            'estado_sofia' => 0,
        ]);

        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
        ]);

        $this->httpClientMock->shouldReceive('validate')
            ->andReturn('NO_REGISTRADO');

        $this->stateMapperMock->shouldReceive('mapToState')
            ->andReturn(0);

        $this->stateMapperMock->shouldReceive('getStateLabel')
            ->andReturn('No registrado');

        $this->auditoriaServiceMock->shouldReceive('registrarValidacionSenasofiaplus');

        $this->service->validateAspirante($aspirante, 1);

        $persona->refresh();
        $this->assertEquals(0, $persona->estado_sofia);
    }

    #[Test]
    public function maneja_error_de_validacion(): void
    {
        $persona = Persona::factory()->create([
            'numero_documento' => '1234567890',
            'estado_sofia' => 0,
        ]);

        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
        ]);

        $this->httpClientMock->shouldReceive('validate')
            ->once()
            ->andThrow(new \RuntimeException('Error de conexión'));

        $this->auditoriaServiceMock->shouldReceive('registrarValidacionSenasofiaplus')
            ->once()
            ->with(
                $aspirante->id,
                'error',
                Mockery::type('string'),
                Mockery::type('array')
            );

        $resultado = $this->service->validateAspirante($aspirante, 1);

        $this->assertFalse($resultado['success']);
        $this->assertEquals('1234567890', $resultado['cedula']);
        $this->assertArrayHasKey('error', $resultado);
    }

    #[Test]
    public function actualiza_progreso_cuando_se_proporciona(): void
    {
        $persona = Persona::factory()->create([
            'numero_documento' => '1234567890',
            'estado_sofia' => 0,
        ]);

        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
        ]);

        $progress = SofiaValidationProgress::factory()->create([
            'processed_aspirantes' => 0,
            'successful_validations' => 0,
        ]);

        $this->httpClientMock->shouldReceive('validate')
            ->andReturn('YA_EXISTE');

        $this->stateMapperMock->shouldReceive('mapToState')
            ->andReturn(1);

        $this->stateMapperMock->shouldReceive('getStateLabel')
            ->andReturn('Registrado');

        $this->auditoriaServiceMock->shouldReceive('registrarValidacionSenasofiaplus');

        $this->service->validateAspirante($aspirante, 1, $progress);

        $progress->refresh();
        $this->assertEquals(1, $progress->processed_aspirantes);
        $this->assertEquals(1, $progress->successful_validations);
    }

    #[Test]
    public function obtiene_aspirantes_que_necesitan_validacion(): void
    {
        $programa = \App\Models\ComplementarioOfertado::factory()->create();

        $persona1 = Persona::factory()->create(['estado_sofia' => 0]);
        $persona2 = Persona::factory()->create(['estado_sofia' => 2]);
        $persona3 = Persona::factory()->create(['estado_sofia' => 1]); // Ya validado

        $aspirante1 = AspiranteComplementario::factory()->create([
            'persona_id' => $persona1->id,
            'complementario_id' => $programa->id,
        ]);

        $aspirante2 = AspiranteComplementario::factory()->create([
            'persona_id' => $persona2->id,
            'complementario_id' => $programa->id,
        ]);

        $aspirante3 = AspiranteComplementario::factory()->create([
            'persona_id' => $persona3->id,
            'complementario_id' => $programa->id,
        ]);

        $aspirantes = $this->service->getAspirantesToValidate($programa->id);

        $this->assertCount(2, $aspirantes);
        $this->assertTrue($aspirantes->contains($aspirante1));
        $this->assertTrue($aspirantes->contains($aspirante2));
        $this->assertFalse($aspirantes->contains($aspirante3));
    }

    #[Test]
    public function registra_auditoria_con_datos_correctos(): void
    {
        $persona = Persona::factory()->create([
            'numero_documento' => '1234567890',
            'estado_sofia' => 0,
        ]);

        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
        ]);

        $this->httpClientMock->shouldReceive('validate')
            ->andReturn('YA_EXISTE');

        $this->stateMapperMock->shouldReceive('mapToState')
            ->andReturn(1);

        $this->stateMapperMock->shouldReceive('getStateLabel')
            ->andReturn('Registrado');

        $this->auditoriaServiceMock->shouldReceive('registrarValidacionSenasofiaplus')
            ->once()
            ->with(
                $aspirante->id,
                'exitoso',
                Mockery::on(function ($message) {
                    return str_contains($message, 'Validacion completada');
                }),
                Mockery::on(function ($data) {
                    return isset($data['cedula']) &&
                           isset($data['resultado_api']) &&
                           isset($data['estado_anterior']) &&
                           isset($data['estado_nuevo']) &&
                           $data['cedula'] === '1234567890' &&
                           $data['resultado_api'] === 'YA_EXISTE';
                })
            );

        $this->service->validateAspirante($aspirante, 1);
    }

    #[Test]
    public function incrementa_progreso_con_error_cuando_falla_validacion(): void
    {
        $persona = Persona::factory()->create([
            'numero_documento' => '1234567890',
            'estado_sofia' => 0,
        ]);

        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
        ]);

        $progress = SofiaValidationProgress::factory()->create([
            'processed_aspirantes' => 0,
            'successful_validations' => 0,
        ]);

        $this->httpClientMock->shouldReceive('validate')
            ->andThrow(new \RuntimeException('Error de conexión'));

        $this->auditoriaServiceMock->shouldReceive('registrarValidacionSenasofiaplus');

        $this->service->validateAspirante($aspirante, 1, $progress);

        $progress->refresh();
        $this->assertEquals(1, $progress->processed_aspirantes);
        $this->assertEquals(0, $progress->successful_validations);
    }
}

