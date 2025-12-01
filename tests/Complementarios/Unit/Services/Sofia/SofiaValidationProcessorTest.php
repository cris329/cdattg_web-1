<?php

namespace Tests\Complementarios\Unit\Services\Sofia;

use Tests\TestCase;
use App\Services\Complementarios\Sofia\SofiaValidationProcessor;
use App\Services\Complementarios\Sofia\SofiaValidationService;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Persona;
use App\Models\Complementarios\SofiaValidationProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Mockery;
use Tests\Complementarios\Concerns\SeedsComplementariosDatabase;

class SofiaValidationProcessorTest extends TestCase
{
    use RefreshDatabase;
    use SeedsComplementariosDatabase;

    private const TEST_NUMERO_DOCUMENTO_1 = '1111111111';
    private const TEST_NUMERO_DOCUMENTO_2 = '2222222222';

    private SofiaValidationProcessor $processor;
    private $validationServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedComplementariosDatabaseIfNeeded();

        $this->validationServiceMock = Mockery::mock(SofiaValidationService::class);
        $this->processor = new SofiaValidationProcessor($this->validationServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function puede_procesar_batch_de_aspirantes(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $persona1 = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO_1]);
        $persona2 = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO_2]);

        $aspirante1 = AspiranteComplementario::factory()->create([
            'persona_id' => $persona1->id,
            'complementario_id' => $programa->id,
        ]);

        $aspirante2 = AspiranteComplementario::factory()->create([
            'persona_id' => $persona2->id,
            'complementario_id' => $programa->id,
        ]);

        $aspirantes = collect([$aspirante1, $aspirante2]);

        $this->validationServiceMock->shouldReceive('validateAspirante')
            ->twice()
            ->andReturn([
                'success' => true,
                'cedula' => self::TEST_NUMERO_DOCUMENTO_1,
                'resultado' => 'YA_EXISTE',
                'estado' => 1,
                'duration' => 1.5,
            ], [
                'success' => true,
                'cedula' => self::TEST_NUMERO_DOCUMENTO_2,
                'resultado' => 'NO_REGISTRADO',
                'estado' => 0,
                'duration' => 1.5,
            ]);

        $resultado = $this->processor->processBatch($aspirantes, $programa->id);

        $this->assertEquals(2, $resultado['total']);
        $this->assertEquals(2, $resultado['exitosos']);
        $this->assertEquals(0, $resultado['errores']);
        $this->assertIsArray($resultado['errores_detalle']);
    }

    #[Test]
    public function maneja_errores_en_validacion(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $persona1 = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO_1]);
        $persona2 = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO_2]);

        $aspirante1 = AspiranteComplementario::factory()->create([
            'persona_id' => $persona1->id,
            'complementario_id' => $programa->id,
        ]);

        $aspirante2 = AspiranteComplementario::factory()->create([
            'persona_id' => $persona2->id,
            'complementario_id' => $programa->id,
        ]);

        $aspirantes = collect([$aspirante1, $aspirante2]);

        $this->validationServiceMock->shouldReceive('validateAspirante')
            ->twice()
            ->andReturn([
                'success' => true,
                'cedula' => self::TEST_NUMERO_DOCUMENTO_1,
                'resultado' => 'YA_EXISTE',
                'estado' => 1,
                'duration' => 1.5,
            ], [
                'success' => false,
                'cedula' => self::TEST_NUMERO_DOCUMENTO_2,
                'error' => 'Error de conexión',
            ]);

        $resultado = $this->processor->processBatch($aspirantes, $programa->id);

        $this->assertEquals(2, $resultado['total']);
        $this->assertEquals(1, $resultado['exitosos']);
        $this->assertEquals(1, $resultado['errores']);
        $this->assertCount(1, $resultado['errores_detalle']);
    }

    #[Test]
    public function procesa_aspirantes_en_lotes(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        // Crear 7 aspirantes (se procesarán en 2 lotes de 5 y 2)
        $aspirantes = collect();
        for ($i = 0; $i < 7; $i++) {
            $persona = Persona::factory()->create(['numero_documento' => "111111111{$i}"]);
            $aspirantes->push(AspiranteComplementario::factory()->create([
                'persona_id' => $persona->id,
                'complementario_id' => $programa->id,
            ]));
        }

        $this->validationServiceMock->shouldReceive('validateAspirante')
            ->times(7)
            ->andReturn([
                'success' => true,
                'cedula' => '1111111110',
                'resultado' => 'YA_EXISTE',
                'estado' => 1,
                'duration' => 1.5,
            ]);

        $resultado = $this->processor->processBatch($aspirantes, $programa->id);

        $this->assertEquals(7, $resultado['total']);
        $this->assertEquals(7, $resultado['exitosos']);
    }

    #[Test]
    public function actualiza_progreso_durante_procesamiento(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $persona = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO_1]);
        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);

        $progress = SofiaValidationProgress::factory()->create([
            'total_aspirantes' => 1,
            'processed_aspirantes' => 0,
            'successful_validations' => 0,
        ]);

        $this->validationServiceMock->shouldReceive('validateAspirante')
            ->once()
            ->with($aspirante, $programa->id, $progress)
            ->andReturn([
                'success' => true,
                'cedula' => self::TEST_NUMERO_DOCUMENTO_1,
                'resultado' => 'YA_EXISTE',
                'estado' => 1,
                'duration' => 1.5,
            ]);

        $this->processor->processBatch(collect([$aspirante]), $programa->id, $progress);

        $progress->refresh();
        $this->assertEquals(1, $progress->processed_aspirantes);
    }

    #[Test]
    public function retorna_estadisticas_correctas(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $persona1 = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO_1]);
        $persona2 = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO_2]);
        $persona3 = Persona::factory()->create(['numero_documento' => '3333333333']);

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

        $aspirantes = collect([$aspirante1, $aspirante2, $aspirante3]);

        $this->validationServiceMock->shouldReceive('validateAspirante')
            ->times(3)
            ->andReturn(
                [
                    'success' => true,
                    'estado' => 1, // Registrado
                ],
                [
                    'success' => true,
                    'estado' => 0, // No registrado
                ],
                [
                    'success' => false,
                    'error' => 'Error',
                ]
            );

        $resultado = $this->processor->processBatch($aspirantes, $programa->id);

        $this->assertEquals(3, $resultado['total']);
        $this->assertEquals(2, $resultado['exitosos']); // Solo estados 0, 1, 2 son exitosos
        $this->assertEquals(1, $resultado['errores']);
    }

    #[Test]
    public function procesa_batch_vacio(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $resultado = $this->processor->processBatch(collect(), $programa->id);

        $this->assertEquals(0, $resultado['total']);
        $this->assertEquals(0, $resultado['exitosos']);
        $this->assertEquals(0, $resultado['errores']);
    }

    #[Test]
    public function no_cuenta_estado_invalido_como_exitoso(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $persona = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO_1]);
        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);

        $this->validationServiceMock->shouldReceive('validateAspirante')
            ->once()
            ->andReturn([
                'success' => true,
                'cedula' => self::TEST_NUMERO_DOCUMENTO_1,
                'estado' => 99, // Estado inválido
                'duration' => 1.5,
            ]);

        $resultado = $this->processor->processBatch(collect([$aspirante]), $programa->id);

        $this->assertEquals(1, $resultado['total']);
        $this->assertEquals(0, $resultado['exitosos']); // Estado inválido no cuenta como exitoso
        $this->assertEquals(0, $resultado['errores']);
    }

    #[Test]
    public function cuenta_estado_null_como_no_exitoso(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $persona = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO_1]);
        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);

        $this->validationServiceMock->shouldReceive('validateAspirante')
            ->once()
            ->andReturn([
                'success' => true,
                'cedula' => self::TEST_NUMERO_DOCUMENTO_1,
                'estado' => null, // Estado null
                'duration' => 1.5,
            ]);

        $resultado = $this->processor->processBatch(collect([$aspirante]), $programa->id);

        $this->assertEquals(1, $resultado['total']);
        $this->assertEquals(0, $resultado['exitosos']); // Estado null no cuenta como exitoso
        $this->assertEquals(0, $resultado['errores']);
    }

    #[Test]
    public function actualiza_progreso_con_resultado_exitoso(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $persona = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO_1]);
        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);

        $progress = SofiaValidationProgress::factory()->create([
            'total_aspirantes' => 1,
            'processed_aspirantes' => 0,
            'successful_validations' => 0,
        ]);

        $this->validationServiceMock->shouldReceive('validateAspirante')
            ->once()
            ->with($aspirante, $programa->id, $progress)
            ->andReturn([
                'success' => true,
                'cedula' => self::TEST_NUMERO_DOCUMENTO_1,
                'resultado' => 'YA_EXISTE',
                'estado' => 1,
                'duration' => 1.5,
            ]);

        $this->processor->processBatch(collect([$aspirante]), $programa->id, $progress);

        $progress->refresh();
        $this->assertEquals(1, $progress->processed_aspirantes);
        $this->assertEquals(1, $progress->successful_validations);
    }

    #[Test]
    public function actualiza_progreso_con_resultado_fallido(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $persona = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO_1]);
        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);

        $progress = SofiaValidationProgress::factory()->create([
            'total_aspirantes' => 1,
            'processed_aspirantes' => 0,
            'successful_validations' => 0,
        ]);

        $this->validationServiceMock->shouldReceive('validateAspirante')
            ->once()
            ->with($aspirante, $programa->id, $progress)
            ->andReturn([
                'success' => false,
                'cedula' => self::TEST_NUMERO_DOCUMENTO_1,
                'error' => 'Error de conexión',
            ]);

        $this->processor->processBatch(collect([$aspirante]), $programa->id, $progress);

        $progress->refresh();
        $this->assertEquals(1, $progress->processed_aspirantes);
        $this->assertEquals(0, $progress->successful_validations);
    }

    #[Test]
    public function no_actualiza_progreso_si_no_se_proporciona(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $persona = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO_1]);
        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);

        $this->validationServiceMock->shouldReceive('validateAspirante')
            ->once()
            ->with($aspirante, $programa->id, null)
            ->andReturn([
                'success' => true,
                'cedula' => self::TEST_NUMERO_DOCUMENTO_1,
                'resultado' => 'YA_EXISTE',
                'estado' => 1,
                'duration' => 1.5,
            ]);

        // No debería lanzar excepción aunque no haya progress
        $resultado = $this->processor->processBatch(collect([$aspirante]), $programa->id, null);

        $this->assertEquals(1, $resultado['total']);
        $this->assertEquals(1, $resultado['exitosos']);
    }

    #[Test]
    public function procesa_multiples_lotes_con_diferentes_tamanos(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        // Crear 12 aspirantes (se procesarán en 3 lotes: 5, 5, 2)
        $aspirantes = collect();
        for ($i = 0; $i < 12; $i++) {
            $persona = Persona::factory()->create(['numero_documento' => "111111111{$i}"]);
            $aspirantes->push(AspiranteComplementario::factory()->create([
                'persona_id' => $persona->id,
                'complementario_id' => $programa->id,
            ]));
        }

        $this->validationServiceMock->shouldReceive('validateAspirante')
            ->times(12)
            ->andReturn([
                'success' => true,
                'cedula' => '1111111110',
                'resultado' => 'YA_EXISTE',
                'estado' => 1,
                'duration' => 1.5,
            ]);

        $resultado = $this->processor->processBatch($aspirantes, $programa->id);

        $this->assertEquals(12, $resultado['total']);
        $this->assertEquals(12, $resultado['exitosos']);
    }

    #[Test]
    public function maneja_errores_detalle_correctamente(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $persona1 = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO_1]);
        $persona2 = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO_2]);

        $aspirante1 = AspiranteComplementario::factory()->create([
            'persona_id' => $persona1->id,
            'complementario_id' => $programa->id,
        ]);

        $aspirante2 = AspiranteComplementario::factory()->create([
            'persona_id' => $persona2->id,
            'complementario_id' => $programa->id,
        ]);

        $aspirantes = collect([$aspirante1, $aspirante2]);

        $this->validationServiceMock->shouldReceive('validateAspirante')
            ->twice()
            ->andReturn(
                [
                    'success' => false,
                    'cedula' => self::TEST_NUMERO_DOCUMENTO_1,
                    'error' => 'Error de conexión 1',
                ],
                [
                    'success' => false,
                    'cedula' => self::TEST_NUMERO_DOCUMENTO_2,
                    'error' => 'Error de conexión 2',
                ]
            );

        $resultado = $this->processor->processBatch($aspirantes, $programa->id);

        $this->assertEquals(2, $resultado['total']);
        $this->assertEquals(0, $resultado['exitosos']);
        $this->assertEquals(2, $resultado['errores']);
        $this->assertCount(2, $resultado['errores_detalle']);
        $this->assertContains('Error de conexión 1', $resultado['errores_detalle']);
        $this->assertContains('Error de conexión 2', $resultado['errores_detalle']);
    }

    #[Test]
    public function maneja_error_sin_mensaje_detallado(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $persona = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO_1]);
        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);

        $this->validationServiceMock->shouldReceive('validateAspirante')
            ->once()
            ->andReturn([
                'success' => false,
                'cedula' => self::TEST_NUMERO_DOCUMENTO_1,
                // Sin campo 'error'
            ]);

        $resultado = $this->processor->processBatch(collect([$aspirante]), $programa->id);

        $this->assertEquals(1, $resultado['total']);
        $this->assertEquals(0, $resultado['exitosos']);
        $this->assertEquals(1, $resultado['errores']);
        $this->assertCount(1, $resultado['errores_detalle']);
        $this->assertEquals('Error desconocido', $resultado['errores_detalle'][0]);
    }

    #[Test]
    public function cuenta_todos_los_estados_validos_como_exitosos(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $persona1 = Persona::factory()->create(['numero_documento' => '1111111111']);
        $persona2 = Persona::factory()->create(['numero_documento' => '2222222222']);
        $persona3 = Persona::factory()->create(['numero_documento' => '3333333333']);

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

        $aspirantes = collect([$aspirante1, $aspirante2, $aspirante3]);

        $this->validationServiceMock->shouldReceive('validateAspirante')
            ->times(3)
            ->andReturn(
                [
                    'success' => true,
                    'estado' => 0, // No registrado - válido
                ],
                [
                    'success' => true,
                    'estado' => 1, // Registrado - válido
                ],
                [
                    'success' => true,
                    'estado' => 2, // Requiere cambio - válido
                ]
            );

        $resultado = $this->processor->processBatch($aspirantes, $programa->id);

        $this->assertEquals(3, $resultado['total']);
        $this->assertEquals(3, $resultado['exitosos']); // Todos los estados válidos
        $this->assertEquals(0, $resultado['errores']);
    }
}

