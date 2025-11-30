<?php

namespace Tests\Complementarios\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\ValidarSofiaJob;
use App\Services\Sofia\SofiaValidationService;
use App\Services\Sofia\SofiaValidationProcessor;
use App\Models\ComplementarioOfertado;
use App\Models\AspiranteComplementario;
use App\Models\Persona;
use App\Models\SofiaValidationProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Mockery;

class ValidarSofiaJobTest extends TestCase
{
    use RefreshDatabase;

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
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function puede_crear_job(): void
    {
        $job = new ValidarSofiaJob(1, 1, 1);

        $this->assertInstanceOf(ValidarSofiaJob::class, $job);
        $this->assertEquals(1, $job->complementarioId);
        $this->assertEquals(1, $job->userId);
        $this->assertEquals(1, $job->progressId);
    }

    #[Test]
    public function puede_crear_job_sin_progreso(): void
    {
        $job = new ValidarSofiaJob(1, 1, null);

        $this->assertInstanceOf(ValidarSofiaJob::class, $job);
        $this->assertEquals(1, $job->complementarioId);
        $this->assertNull($job->progressId);
    }

    #[Test]
    public function ejecuta_job_y_procesa_aspirantes(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $persona = Persona::factory()->create(['estado_sofia' => 0]);
        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);

        $progress = SofiaValidationProgress::factory()->create([
            'complementario_id' => $programa->id,
            'status' => 'pending',
            'total_aspirantes' => 1,
        ]);

        $validationServiceMock = Mockery::mock(SofiaValidationService::class);
        $processorMock = Mockery::mock(SofiaValidationProcessor::class);

        $validationServiceMock->shouldReceive('getAspirantesToValidate')
            ->once()
            ->with($programa->id)
            ->andReturn(collect([$aspirante]));

        $processorMock->shouldReceive('processBatch')
            ->once()
            ->with(
                Mockery::type('Illuminate\Support\Collection'),
                $programa->id,
                $progress
            )
            ->andReturn([
                'total' => 1,
                'exitosos' => 1,
                'errores' => 0,
                'errores_detalle' => [],
            ]);

        $job = new ValidarSofiaJob($programa->id, 1, $progress->id);
        $job->handle($validationServiceMock, $processorMock);

        $progress->refresh();
        $this->assertEquals('completed', $progress->status);
    }

    #[Test]
    public function marca_progreso_como_completado_si_no_hay_aspirantes(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $progress = SofiaValidationProgress::factory()->create([
            'complementario_id' => $programa->id,
            'status' => 'pending',
        ]);

        $validationServiceMock = Mockery::mock(SofiaValidationService::class);
        $processorMock = Mockery::mock(SofiaValidationProcessor::class);

        $validationServiceMock->shouldReceive('getAspirantesToValidate')
            ->once()
            ->with($programa->id)
            ->andReturn(collect());

        $job = new ValidarSofiaJob($programa->id, 1, $progress->id);
        $job->handle($validationServiceMock, $processorMock);

        $progress->refresh();
        $this->assertEquals('completed', $progress->status);
    }

    #[Test]
    public function inicializa_progreso_al_iniciar_job(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $progress = SofiaValidationProgress::factory()->create([
            'complementario_id' => $programa->id,
            'status' => 'pending',
        ]);

        $validationServiceMock = Mockery::mock(SofiaValidationService::class);
        $processorMock = Mockery::mock(SofiaValidationProcessor::class);

        $validationServiceMock->shouldReceive('getAspirantesToValidate')
            ->andReturn(collect());

        $job = new ValidarSofiaJob($programa->id, 1, $progress->id);
        $job->handle($validationServiceMock, $processorMock);

        $progress->refresh();
        $this->assertEquals('processing', $progress->status);
        $this->assertNotNull($progress->started_at);
    }

    #[Test]
    public function marca_progreso_como_fallido_si_hay_errores(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $persona = Persona::factory()->create(['estado_sofia' => 0]);
        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);

        $progress = SofiaValidationProgress::factory()->create([
            'complementario_id' => $programa->id,
            'status' => 'pending',
        ]);

        $validationServiceMock = Mockery::mock(SofiaValidationService::class);
        $processorMock = Mockery::mock(SofiaValidationProcessor::class);

        $validationServiceMock->shouldReceive('getAspirantesToValidate')
            ->andReturn(collect([$aspirante]));

        $processorMock->shouldReceive('processBatch')
            ->andReturn([
                'total' => 1,
                'exitosos' => 0,
                'errores' => 1,
                'errores_detalle' => ['Error de conexión'],
            ]);

        $job = new ValidarSofiaJob($programa->id, 1, $progress->id);
        $job->handle($validationServiceMock, $processorMock);

        $progress->refresh();
        $this->assertEquals('failed', $progress->status);
    }

    #[Test]
    public function maneja_job_sin_progreso(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        $validationServiceMock = Mockery::mock(SofiaValidationService::class);
        $processorMock = Mockery::mock(SofiaValidationProcessor::class);

        $validationServiceMock->shouldReceive('getAspirantesToValidate')
            ->andReturn(collect());

        $job = new ValidarSofiaJob($programa->id, 1, null);
        $job->handle($validationServiceMock, $processorMock);

        // No debería lanzar excepción
        $this->assertTrue(true);
    }
}
