<?php

namespace Tests\Complementarios\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\Complementarios\ValidarSofiaJob;
use App\Services\Complementarios\Sofia\SofiaValidationService;
use App\Services\Complementarios\Sofia\SofiaValidationProcessor;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Persona;
use App\Models\Complementarios\SofiaValidationProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Mockery;

class ValidarSofiaJobTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    private function createTestUser(): \App\Models\User
    {
        static $userCount = 0;
        $userCount++;

        // Obtener datos del seeder
        $pais = \App\Models\Pais::first();
        $departamento = \App\Models\Departamento::where('pais_id', $pais->id)->first();
        $municipio = \App\Models\Municipio::where('departamento_id', $departamento->id)->first();

        // Obtener parametros_temas correctos
        $tipoDocumento = \App\Models\ParametroTema::where('tema_id', 2)
            ->where('parametro_id', 3)
            ->first();
        $genero = \App\Models\ParametroTema::where('tema_id', 3)
            ->where('parametro_id', 9)
            ->first();

        if (!$tipoDocumento) {
            $tipoDocumento = \App\Models\ParametroTema::where('tema_id', 2)->first();
        }
        if (!$genero) {
            $genero = \App\Models\ParametroTema::where('tema_id', 3)->first();
        }

        $persona = \App\Models\Persona::create([
            'tipo_documento' => $tipoDocumento->id ?? 1,
            'numero_documento' => '123456' . $userCount . uniqid(),
            'primer_nombre' => 'Test' . $userCount,
            'segundo_nombre' => '',
            'primer_apellido' => 'User' . $userCount,
            'segundo_apellido' => '',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => $genero->id ?? 1,
            'telefono' => '123456789' . $userCount,
            'celular' => '098765432' . $userCount,
            'email' => 'test' . $userCount . uniqid() . '@example.com',
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => 'Dirección test' . $userCount,
            'status' => 1,
            'estado_sofia' => 1,
        ]);

        return \App\Models\User::create([
            'email' => 'test' . $userCount . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
            'persona_id' => $persona->id,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Ejecutar seeders necesarios para las pruebas
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
        $this->assertEquals(1, $job->getComplementarioId());
        $this->assertEquals(1, $job->getUserId());
        $this->assertEquals(1, $job->getProgressId());
    }

    #[Test]
    public function puede_crear_job_sin_progreso(): void
    {
        $job = new ValidarSofiaJob(1, 1, null);

        $this->assertInstanceOf(ValidarSofiaJob::class, $job);
        $this->assertEquals(1, $job->getComplementarioId());
        $this->assertNull($job->getProgressId());
    }

    #[Test]
    public function ejecuta_job_y_procesa_aspirantes(): void
    {
        // Obtener datos necesarios del seeder
        $modalidad = \App\Models\ParametroTema::where('tema_id', 5)
            ->whereIn('parametro_id', [18, 19, 20])
            ->first();
        $jornada = \App\Models\JornadaFormacion::first();
        $ambiente = \App\Models\Ambiente::first();
        $pais = \App\Models\Pais::first();
        $departamento = \App\Models\Departamento::where('pais_id', $pais->id)->first();
        $municipio = \App\Models\Municipio::where('departamento_id', $departamento->id)->first();
        $tipoDocumento = \App\Models\ParametroTema::where('tema_id', 2)
            ->where('parametro_id', 3)
            ->first();
        $genero = \App\Models\ParametroTema::where('tema_id', 3)
            ->where('parametro_id', 9)
            ->first();

        if (!$tipoDocumento) {
            $tipoDocumento = \App\Models\ParametroTema::where('tema_id', 2)->first();
        }
        if (!$genero) {
            $genero = \App\Models\ParametroTema::where('tema_id', 3)->first();
        }

        // Crear programa complementario
        $programa = ComplementarioOfertado::create([
            'codigo' => 'TEST001',
            'nombre' => 'Programa Test',
            'justificacion' => 'Justificación test',
            'requisitos_ingreso' => 'Requisitos test',
            'estado' => 1,
            'duracion' => 30,
            'cupos' => 50,
            'modalidad_id' => $modalidad->id,
            'jornada_id' => $jornada->id,
            'ambiente_id' => $ambiente->id,
        ]);

        // Crear persona
        $persona = Persona::create([
            'tipo_documento' => $tipoDocumento->id,
            'numero_documento' => '123456789',
            'primer_nombre' => 'Test',
            'segundo_nombre' => '',
            'primer_apellido' => 'User',
            'segundo_apellido' => '',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => $genero->id,
            'telefono' => '1234567890',
            'celular' => '0987654321',
            'email' => 'test@example.com',
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => 'Dirección test',
            'status' => 1,
            'estado_sofia' => 0,
        ]);

        $aspirante = AspiranteComplementario::create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
            'estado' => 1,
        ]);

        $progress = SofiaValidationProgress::create([
            'complementario_id' => $programa->id,
            'user_id' => 1,
            'status' => 'pending',
            'total_aspirantes' => 1,
            'processed_aspirantes' => 0,
            'successful_validations' => 0,
            'failed_validations' => 0,
        ]);

        $validationServiceMock = Mockery::mock(SofiaValidationService::class);
        $processorMock = Mockery::mock(SofiaValidationProcessor::class);

        $validationServiceMock->shouldReceive('getAspirantesToValidate')
            ->once()
            ->with($programa->id)
            ->andReturn(collect([$aspirante]));

        $processorMock->shouldReceive('processBatch')
            ->once()
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
        $user = $this->createTestUser();
        
        // Obtener datos necesarios del seeder
        $modalidad = \App\Models\ParametroTema::where('tema_id', 5)
            ->whereIn('parametro_id', [18, 19, 20])
            ->first();
        $jornada = \App\Models\JornadaFormacion::first();
        $ambiente = \App\Models\Ambiente::first();

        $programa = ComplementarioOfertado::create([
            'codigo' => 'TEST002',
            'nombre' => 'Programa Test 2',
            'justificacion' => 'Justificación test 2',
            'requisitos_ingreso' => 'Requisitos test 2',
            'estado' => 1,
            'duracion' => 30,
            'cupos' => 50,
            'modalidad_id' => $modalidad->id,
            'jornada_id' => $jornada->id,
            'ambiente_id' => $ambiente->id,
        ]);

        $progress = SofiaValidationProgress::create([
            'complementario_id' => $programa->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'total_aspirantes' => 0,
            'processed_aspirantes' => 0,
            'successful_validations' => 0,
            'failed_validations' => 0,
        ]);

        $validationServiceMock = Mockery::mock(SofiaValidationService::class);
        $processorMock = Mockery::mock(SofiaValidationProcessor::class);

        $validationServiceMock->shouldReceive('getAspirantesToValidate')
            ->once()
            ->with($programa->id)
            ->andReturn(collect());

        $job = new ValidarSofiaJob($programa->id, $user->id, $progress->id);
        $job->handle($validationServiceMock, $processorMock);

        $progress->refresh();
        $this->assertEquals('completed', $progress->status);
    }

    #[Test]
    public function inicializa_progreso_al_iniciar_job(): void
    {
        $user = $this->createTestUser();
        
        // Obtener datos necesarios del seeder
        $modalidad = \App\Models\ParametroTema::where('tema_id', 5)
            ->whereIn('parametro_id', [18, 19, 20])
            ->first();
        $jornada = \App\Models\JornadaFormacion::first();
        $ambiente = \App\Models\Ambiente::first();
        $pais = \App\Models\Pais::first();
        $departamento = \App\Models\Departamento::where('pais_id', $pais->id)->first();
        $municipio = \App\Models\Municipio::where('departamento_id', $departamento->id)->first();
        $tipoDocumento = \App\Models\ParametroTema::where('tema_id', 2)
            ->where('parametro_id', 3)
            ->first();
        $genero = \App\Models\ParametroTema::where('tema_id', 3)
            ->where('parametro_id', 9)
            ->first();

        if (!$tipoDocumento) {
            $tipoDocumento = \App\Models\ParametroTema::where('tema_id', 2)->first();
        }
        if (!$genero) {
            $genero = \App\Models\ParametroTema::where('tema_id', 3)->first();
        }

        $programa = ComplementarioOfertado::create([
            'codigo' => 'TEST003',
            'nombre' => 'Programa Test 3',
            'justificacion' => 'Justificación test 3',
            'requisitos_ingreso' => 'Requisitos test 3',
            'estado' => 1,
            'duracion' => 30,
            'cupos' => 50,
            'modalidad_id' => $modalidad->id,
            'jornada_id' => $jornada->id,
            'ambiente_id' => $ambiente->id,
        ]);

        // Crear persona y aspirante para que el Job procese y mantenga el estado 'processing'
        $persona = Persona::create([
            'tipo_documento' => $tipoDocumento->id,
            'numero_documento' => '1234567890',
            'primer_nombre' => 'Test',
            'segundo_nombre' => '',
            'primer_apellido' => 'User',
            'segundo_apellido' => '',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => $genero->id,
            'telefono' => '1234567890',
            'celular' => '0987654321',
            'email' => 'testuser@example.com',
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => 'Dirección test',
            'status' => 1,
            'estado_sofia' => 0,
        ]);

        $aspirante = AspiranteComplementario::create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
            'estado' => 1,
        ]);

        $progress = SofiaValidationProgress::create([
            'complementario_id' => $programa->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'total_aspirantes' => 1,
            'processed_aspirantes' => 0,
            'successful_validations' => 0,
            'failed_validations' => 0,
        ]);

        $validationServiceMock = Mockery::mock(SofiaValidationService::class);
        $processorMock = Mockery::mock(SofiaValidationProcessor::class);

        // Devolver aspirantes para que el Job procese y mantenga el estado 'processing'
        $validationServiceMock->shouldReceive('getAspirantesToValidate')
            ->andReturn(collect([$aspirante]));

        // Mockear el procesamiento para que no complete automáticamente
        $processorMock->shouldReceive('processBatch')
            ->andReturn([
                'total' => 1,
                'exitosos' => 1,
                'errores' => 0,
                'errores_detalle' => [],
            ]);

        $job = new ValidarSofiaJob($programa->id, $user->id, $progress->id);
        $job->handle($validationServiceMock, $processorMock);

        $progress->refresh();
        // Después de procesar exitosamente, el estado debería ser 'completed'
        $this->assertEquals('completed', $progress->status);
        $this->assertNotNull($progress->started_at);
    }

    #[Test]
    public function marca_progreso_como_fallido_si_hay_errores(): void
    {
        $user = $this->createTestUser();
        
        // Obtener datos necesarios del seeder
        $modalidad = \App\Models\ParametroTema::where('tema_id', 5)
            ->whereIn('parametro_id', [18, 19, 20])
            ->first();
        $jornada = \App\Models\JornadaFormacion::first();
        $ambiente = \App\Models\Ambiente::first();
        $pais = \App\Models\Pais::first();
        $departamento = \App\Models\Departamento::where('pais_id', $pais->id)->first();
        $municipio = \App\Models\Municipio::where('departamento_id', $departamento->id)->first();
        $tipoDocumento = \App\Models\ParametroTema::where('tema_id', 2)
            ->where('parametro_id', 3)
            ->first();
        $genero = \App\Models\ParametroTema::where('tema_id', 3)
            ->where('parametro_id', 9)
            ->first();

        if (!$tipoDocumento) {
            $tipoDocumento = \App\Models\ParametroTema::where('tema_id', 2)->first();
        }
        if (!$genero) {
            $genero = \App\Models\ParametroTema::where('tema_id', 3)->first();
        }

        $programa = ComplementarioOfertado::create([
            'codigo' => 'TEST004',
            'nombre' => 'Programa Test 4',
            'justificacion' => 'Justificación test 4',
            'requisitos_ingreso' => 'Requisitos test 4',
            'estado' => 1,
            'duracion' => 30,
            'cupos' => 50,
            'modalidad_id' => $modalidad->id,
            'jornada_id' => $jornada->id,
            'ambiente_id' => $ambiente->id,
        ]);

        $persona = \App\Models\Persona::create([
            'tipo_documento' => $tipoDocumento->id,
            'numero_documento' => '987654321',
            'primer_nombre' => 'Test',
            'segundo_nombre' => '',
            'primer_apellido' => 'Persona',
            'segundo_apellido' => '',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => $genero->id,
            'telefono' => '1234567890',
            'celular' => '0987654321',
            'email' => 'persona@example.com',
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => 'Dirección test',
            'status' => 1,
            'estado_sofia' => 0,
        ]);

        $aspirante = AspiranteComplementario::create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
            'estado' => 1,
        ]);

        $progress = SofiaValidationProgress::create([
            'complementario_id' => $programa->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'total_aspirantes' => 1,
            'processed_aspirantes' => 0,
            'successful_validations' => 0,
            'failed_validations' => 0,
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

        $job = new ValidarSofiaJob($programa->id, $user->id, $progress->id);
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
