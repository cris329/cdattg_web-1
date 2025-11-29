<?php

namespace Tests\Complementarios\Unit\Models;

use App\Models\AspiranteComplementario;
use App\Models\SenasofiaplusValidationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SenasofiaplusValidationLogModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);
    }

    #[Test]
    public function tiene_relacion_con_aspirante(): void
    {
        $aspirante = AspiranteComplementario::factory()->create();
        $log = SenasofiaplusValidationLog::factory()->create([
            'aspirante_id' => $aspirante->id,
        ]);

        $this->assertInstanceOf(AspiranteComplementario::class, $log->aspirante);
        $this->assertEquals($aspirante->id, $log->aspirante->id);
    }

    #[Test]
    public function tiene_relacion_con_user(): void
    {
        $user = User::factory()->create();
        $log = SenasofiaplusValidationLog::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $log->user);
        $this->assertEquals($user->id, $log->user->id);
    }

    #[Test]
    public function puede_crear_log(): void
    {
        $aspirante = AspiranteComplementario::factory()->create();
        $user = User::factory()->create();

        $log = SenasofiaplusValidationLog::crearLog([
            'aspirante_id' => $aspirante->id,
            'accion' => 'validar',
            'resultado' => 'exitoso',
            'mensaje' => 'Validación exitosa',
            'user_id' => $user->id,
            'fecha_accion' => now(),
        ]);

        $this->assertDatabaseHas('senasofiaplus_validation_logs', [
            'id' => $log->id,
            'resultado' => 'exitoso',
        ]);
    }

    #[Test]
    public function obtiene_logs_por_aspirante(): void
    {
        $aspirante = AspiranteComplementario::factory()->create();
        SenasofiaplusValidationLog::factory()->count(3)->create([
            'aspirante_id' => $aspirante->id,
        ]);

        $logs = SenasofiaplusValidationLog::getLogsPorAspirante($aspirante->id, 10);

        $this->assertCount(3, $logs);
    }

    #[Test]
    public function obtiene_estadisticas_validaciones(): void
    {
        SenasofiaplusValidationLog::factory()->count(2)->create(['resultado' => 'exitoso']);
        SenasofiaplusValidationLog::factory()->count(1)->create(['resultado' => 'error']);

        $estadisticas = SenasofiaplusValidationLog::getEstadisticasValidaciones(
            now()->subDays(7)->format('Y-m-d'),
            now()->format('Y-m-d')
        );

        $this->assertIsArray($estadisticas);
        $this->assertArrayHasKey('total_validaciones', $estadisticas);
    }
}

