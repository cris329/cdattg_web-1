<?php

namespace Tests\Complementarios\Unit\Models;

use App\Models\ComplementarioOfertado;
use App\Models\SofiaValidationProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SofiaValidationProgressModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);
    }

    #[Test]
    public function tiene_relacion_con_complementario(): void
    {
        $complementario = ComplementarioOfertado::factory()->create();
        $progress = SofiaValidationProgress::factory()->create([
            'complementario_id' => $complementario->id,
        ]);

        $this->assertInstanceOf(ComplementarioOfertado::class, $progress->complementario);
        $this->assertEquals($complementario->id, $progress->complementario->id);
    }

    #[Test]
    public function tiene_relacion_con_user(): void
    {
        $user = User::factory()->create();
        $progress = SofiaValidationProgress::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $progress->user);
        $this->assertEquals($user->id, $progress->user->id);
    }

    #[Test]
    public function calcula_porcentaje_progreso(): void
    {
        $progress = SofiaValidationProgress::factory()->create([
            'total_aspirantes' => 100,
            'processed_aspirantes' => 50,
        ]);

        $this->assertEquals(50.0, $progress->progress_percentage);
    }

    #[Test]
    public function obtiene_status_label(): void
    {
        $progress = SofiaValidationProgress::factory()->create(['status' => 'completed']);

        $this->assertEquals('Completado', $progress->status_label);
    }

    #[Test]
    public function marca_como_iniciado(): void
    {
        $progress = SofiaValidationProgress::factory()->create(['status' => 'pending']);

        $progress->markAsStarted();

        $this->assertEquals('processing', $progress->fresh()->status);
        $this->assertNotNull($progress->fresh()->started_at);
    }

    #[Test]
    public function marca_como_completado(): void
    {
        $progress = SofiaValidationProgress::factory()->create(['status' => 'processing']);

        $progress->markAsCompleted();

        $this->assertEquals('completed', $progress->fresh()->status);
        $this->assertNotNull($progress->fresh()->completed_at);
    }

    #[Test]
    public function incrementa_procesados(): void
    {
        $progress = SofiaValidationProgress::factory()->create([
            'processed_aspirantes' => 0,
            'successful_validations' => 0,
        ]);

        $progress->incrementProcessed(true);

        $progress->refresh();
        $this->assertEquals(1, $progress->processed_aspirantes);
        $this->assertEquals(1, $progress->successful_validations);
    }
}

