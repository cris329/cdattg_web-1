<?php

namespace Tests\Complementarios\Feature\Controllers;

use Tests\TestCase;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Persona;
use App\Models\Complementarios\SofiaValidationProgress;
use App\Models\User;
use App\Jobs\Complementarios\ValidarSofiaJob;
use App\Services\Complementarios\Sofia\SofiaParametrosHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\Complementarios\Concerns\SeedsComplementariosDatabase;

class ValidacionSofiaControllerTest extends TestCase
{
    use RefreshDatabase;
    use SeedsComplementariosDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Desactivar CSRF para tests
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);

        $this->seedComplementariosDatabaseIfNeeded();

        // Asegurar que los parámetros de Sofía existan
        SofiaParametrosHelper::clearCache();
        
        // Forzar la creación de parámetros si no existen
        if (!\App\Models\Parametro::where('name', 'NO REGISTRADO')->exists()) {
            SofiaParametrosHelper::crearParametrosSiNoExisten();
        }
        
        SofiaParametrosHelper::clearCache();
        
        // Verificar que los parámetros existan
        $noRegistradoId = SofiaParametrosHelper::getNoRegistradoId();
        $requiereCambioId = SofiaParametrosHelper::getRequiereCambioId();
        $pendingId = SofiaParametrosHelper::getPendingId();
        
        // Si aún no existen, hay un problema
        if (!$noRegistradoId || !$requiereCambioId || !$pendingId) {
            throw new \RuntimeException(
                'Los parámetros de Sofía no se pudieron crear. ' .
                "NO REGISTRADO: " . ($noRegistradoId ?? 'null') . ", " .
                "REQUIERE CAMBIO: " . ($requiereCambioId ?? 'null') . ", " .
                "PENDING: " . ($pendingId ?? 'null')
            );
        }
        
        $this->user = User::factory()->create();
        Queue::fake();
    }

    #[Test]
    public function puede_iniciar_validacion_sofia(): void
    {
        $this->actingAs($this->user);

        $programa = ComplementarioOfertado::factory()->create();

        // Asegurar que los parámetros existan antes de usarlos
        SofiaParametrosHelper::clearCache();
        $noRegistradoId = SofiaParametrosHelper::getNoRegistradoId();
        $requiereCambioId = SofiaParametrosHelper::getRequiereCambioId();
        
        // Verificar que los IDs no sean null
        $this->assertNotNull($noRegistradoId, 'El parámetro NO REGISTRADO debe existir');
        $this->assertNotNull($requiereCambioId, 'El parámetro REQUIERE CAMBIO debe existir');
        
        $persona1 = Persona::factory()->create(['estado_sofia' => $noRegistradoId]);
        $persona2 = Persona::factory()->create(['estado_sofia' => $requiereCambioId]);

        AspiranteComplementario::factory()->create([
            'persona_id' => $persona1->id,
            'complementario_id' => $programa->id,
        ]);

        AspiranteComplementario::factory()->create([
            'persona_id' => $persona2->id,
            'complementario_id' => $programa->id,
        ]);

        $response = $this->post(route('programas-complementarios.validar-sofia', $programa->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'aspirantes_count',
            'progress_id',
        ]);

        Queue::assertPushed(ValidarSofiaJob::class);

        $pendingId = SofiaParametrosHelper::getPendingId();
        $this->assertDatabaseHas('sofia_validation_progress', [
            'complementario_id' => $programa->id,
            'user_id' => $this->user->id,
            'status' => $pendingId,
        ]);
    }

    #[Test]
    public function no_inicia_validacion_si_no_hay_aspirantes(): void
    {
        $this->actingAs($this->user);

        $programa = ComplementarioOfertado::factory()->create();

        // Crear aspirantes pero todos ya validados
        $registradoId = SofiaParametrosHelper::getRegistradoId();
        $persona = Persona::factory()->create(['estado_sofia' => $registradoId]);
        AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);

        $response = $this->post(route('programas-complementarios.validar-sofia', $programa->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => 'No hay aspirantes que necesiten validación en este programa.',
        ]);

        Queue::assertNothingPushed();
    }

    #[Test]
    public function no_inicia_validacion_si_ya_hay_una_en_progreso(): void
    {
        $this->actingAs($this->user);

        $programa = ComplementarioOfertado::factory()->create();

        $noRegistradoId = SofiaParametrosHelper::getNoRegistradoId();
        $processingId = SofiaParametrosHelper::getProcessingId();
        $persona = Persona::factory()->create(['estado_sofia' => $noRegistradoId]);
        AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);

        // Crear validación en progreso
        SofiaValidationProgress::factory()->create([
            'complementario_id' => $programa->id,
            'status' => $processingId,
        ]);

        $response = $this->post(route('programas-complementarios.validar-sofia', $programa->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => 'Ya hay una validación en progreso para este programa. Espere a que termine.',
        ]);

        Queue::assertNothingPushed();
    }

    #[Test]
    public function retorna_error_si_programa_no_existe(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('programas-complementarios.validar-sofia', 99999));

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Programa no encontrado.',
        ]);

        Queue::assertNothingPushed();
    }

    #[Test]
    public function puede_obtener_progreso_de_validacion(): void
    {
        $this->actingAs($this->user);

        $programa = ComplementarioOfertado::factory()->create();
        $processingId = SofiaParametrosHelper::getProcessingId();
        $progress = SofiaValidationProgress::factory()->create([
            'complementario_id' => $programa->id,
            'status' => $processingId,
            'total_aspirantes' => 10,
            'processed_aspirantes' => 5,
            'successful_validations' => 4,
            'failed_validations' => 1,
        ]);

        $response = $this->get(route('sofia-validation.progress', $progress->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'progress' => [
                'id',
                'status',
                'status_label',
                'total_aspirantes',
                'processed_aspirantes',
                'successful_validations',
                'failed_validations',
                'progress_percentage',
                'started_at',
                'completed_at',
                'errors',
            ],
        ]);

        $responseData = $response->json();
        $this->assertEquals($progress->id, $responseData['progress']['id']);
        $processingId = SofiaParametrosHelper::getProcessingId();
        $this->assertEquals($processingId, $responseData['progress']['status']);
        $this->assertEquals(10, $responseData['progress']['total_aspirantes']);
        $this->assertEquals(5, $responseData['progress']['processed_aspirantes']);
    }

    #[Test]
    public function retorna_error_si_progreso_no_existe(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('sofia-validation.progress', 99999));

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
    }

    #[Test]
    public function crea_registro_de_progreso_con_datos_correctos(): void
    {
        $this->actingAs($this->user);

        $programa = ComplementarioOfertado::factory()->create();

        $noRegistradoId = SofiaParametrosHelper::getNoRegistradoId();
        $requiereCambioId = SofiaParametrosHelper::getRequiereCambioId();
        $persona1 = Persona::factory()->create(['estado_sofia' => $noRegistradoId]);
        $persona2 = Persona::factory()->create(['estado_sofia' => $requiereCambioId]);

        AspiranteComplementario::factory()->create([
            'persona_id' => $persona1->id,
            'complementario_id' => $programa->id,
        ]);

        AspiranteComplementario::factory()->create([
            'persona_id' => $persona2->id,
            'complementario_id' => $programa->id,
        ]);

        $response = $this->post(route('programas-complementarios.validar-sofia', $programa->id));

        $response->assertStatus(200);

        $this->assertDatabaseHas('sofia_validation_progress', [
            'complementario_id' => $programa->id,
            'user_id' => $this->user->id,
            'status' => SofiaParametrosHelper::getPendingId(),
            'total_aspirantes' => 2,
            'processed_aspirantes' => 0,
            'successful_validations' => 0,
            'failed_validations' => 0,
        ]);
    }

    #[Test]
    public function despacha_job_con_configuracion_correcta(): void
    {
        $this->actingAs($this->user);

        $programa = ComplementarioOfertado::factory()->create();

        $noRegistradoId = SofiaParametrosHelper::getNoRegistradoId();
        $persona = Persona::factory()->create(['estado_sofia' => $noRegistradoId]);
        AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);

        $this->post(route('programas-complementarios.validar-sofia', $programa->id));

        Queue::assertPushed(ValidarSofiaJob::class);
    }

    #[Test]
    public function no_inicia_validacion_si_hay_validacion_pending(): void
    {
        $this->actingAs($this->user);

        $programa = ComplementarioOfertado::factory()->create();

        $noRegistradoId = SofiaParametrosHelper::getNoRegistradoId();
        $persona = Persona::factory()->create(['estado_sofia' => $noRegistradoId]);
        AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);

        // Crear validación pending
        SofiaValidationProgress::factory()->create([
            'complementario_id' => $programa->id,
            'status' => SofiaParametrosHelper::getPendingId(),
        ]);

        $response = $this->post(route('programas-complementarios.validar-sofia', $programa->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => 'Ya hay una validación en progreso para este programa. Espere a que termine.',
        ]);

        Queue::assertNothingPushed();
    }

    #[Test]
    public function puede_obtener_progreso_completado(): void
    {
        $this->actingAs($this->user);

        $programa = ComplementarioOfertado::factory()->create();
        $progress = SofiaValidationProgress::factory()->create([
            'complementario_id' => $programa->id,
            'status' => SofiaParametrosHelper::getCompletedId(),
            'total_aspirantes' => 10,
            'processed_aspirantes' => 10,
            'successful_validations' => 8,
            'failed_validations' => 2,
            'completed_at' => now(),
        ]);

        $response = $this->get(route('sofia-validation.progress', $progress->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        
        $responseData = $response->json();
        $completedId = SofiaParametrosHelper::getCompletedId();
        $this->assertEquals($completedId, $responseData['progress']['status']);
        $this->assertEquals(10, $responseData['progress']['processed_aspirantes']);
        $this->assertNotNull($responseData['progress']['completed_at']);
    }

    #[Test]
    public function puede_obtener_progreso_failed(): void
    {
        $this->actingAs($this->user);

        $programa = ComplementarioOfertado::factory()->create();
        $progress = SofiaValidationProgress::factory()->create([
            'complementario_id' => $programa->id,
            'status' => SofiaParametrosHelper::getFailedId(),
            'total_aspirantes' => 5,
            'processed_aspirantes' => 3,
            'errors' => ['Error de conexión'],
        ]);

        $response = $this->get(route('sofia-validation.progress', $progress->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        
        $responseData = $response->json();
        $failedId = SofiaParametrosHelper::getFailedId();
        $this->assertEquals($failedId, $responseData['progress']['status']);
        $this->assertNotEmpty($responseData['progress']['errors']);
    }

    #[Test]
    public function cuenta_solo_aspirantes_con_estado_sofia_pendiente(): void
    {
        $this->actingAs($this->user);

        $programa = ComplementarioOfertado::factory()->create();

        // Aspirantes que necesitan validación (NO REGISTRADO o REQUIERE CAMBIO)
        $noRegistradoId = SofiaParametrosHelper::getNoRegistradoId();
        $requiereCambioId = SofiaParametrosHelper::getRequiereCambioId();
        $persona1 = Persona::factory()->create(['estado_sofia' => $noRegistradoId]);
        $persona2 = Persona::factory()->create(['estado_sofia' => $requiereCambioId]);
        
        // Aspirante ya validado
        $registradoId = SofiaParametrosHelper::getRegistradoId();
        $persona3 = Persona::factory()->create(['estado_sofia' => $registradoId]);

        AspiranteComplementario::factory()->create([
            'persona_id' => $persona1->id,
            'complementario_id' => $programa->id,
        ]);
        AspiranteComplementario::factory()->create([
            'persona_id' => $persona2->id,
            'complementario_id' => $programa->id,
        ]);
        AspiranteComplementario::factory()->create([
            'persona_id' => $persona3->id,
            'complementario_id' => $programa->id,
        ]);

        $response = $this->post(route('programas-complementarios.validar-sofia', $programa->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'aspirantes_count' => 2, // Solo los que necesitan validación
        ]);
    }

    #[Test]
    public function maneja_error_interno_al_iniciar_validacion(): void
    {
        $this->actingAs($this->user);

        // Simular error forzando un ID inválido que cause excepción
        // En este caso, el ModelNotFoundException ya está cubierto, pero podemos
        // verificar que otros errores se manejan correctamente
        
        $response = $this->post(route('programas-complementarios.validar-sofia', 'invalid-id'));

        // Debe manejar el error y retornar respuesta JSON
        $this->assertContains($response->status(), [404, 500]);
        $response->assertJson([
            'success' => false,
        ]);
    }

    #[Test]
    public function crea_progreso_con_datos_iniciales_correctos(): void
    {
        $this->actingAs($this->user);

        $programa = ComplementarioOfertado::factory()->create();

        $noRegistradoId = SofiaParametrosHelper::getNoRegistradoId();
        $requiereCambioId = SofiaParametrosHelper::getRequiereCambioId();
        $persona1 = Persona::factory()->create(['estado_sofia' => $noRegistradoId]);
        $persona2 = Persona::factory()->create(['estado_sofia' => $requiereCambioId]);
        $persona3 = Persona::factory()->create(['estado_sofia' => $noRegistradoId]);

        AspiranteComplementario::factory()->create([
            'persona_id' => $persona1->id,
            'complementario_id' => $programa->id,
        ]);
        AspiranteComplementario::factory()->create([
            'persona_id' => $persona2->id,
            'complementario_id' => $programa->id,
        ]);
        AspiranteComplementario::factory()->create([
            'persona_id' => $persona3->id,
            'complementario_id' => $programa->id,
        ]);

        $this->post(route('programas-complementarios.validar-sofia', $programa->id));

        $this->assertDatabaseHas('sofia_validation_progress', [
            'complementario_id' => $programa->id,
            'user_id' => $this->user->id,
            'status' => SofiaParametrosHelper::getPendingId(),
            'total_aspirantes' => 3,
            'processed_aspirantes' => 0,
            'successful_validations' => 0,
            'failed_validations' => 0,
        ]);
    }

    #[Test]
    public function job_se_despacha_en_cola_correcta(): void
    {
        $this->actingAs($this->user);

        $programa = ComplementarioOfertado::factory()->create();

        $noRegistradoId = SofiaParametrosHelper::getNoRegistradoId();
        $persona = Persona::factory()->create(['estado_sofia' => $noRegistradoId]);
        AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);

        $this->post(route('programas-complementarios.validar-sofia', $programa->id));

        Queue::assertPushed(ValidarSofiaJob::class, function ($job) {
            return $job->queue === 'sofia-validation';
        });
    }
}

