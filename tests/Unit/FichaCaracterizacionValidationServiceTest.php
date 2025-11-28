<?php

namespace Tests\Unit;

use App\Models\Ambiente;
use App\Services\FichaCaracterizacionValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FichaCaracterizacionValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FichaCaracterizacionValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
            \Database\Seeders\SedeSeeder::class,
            \Database\Seeders\BloqueSeeder::class,
            \Database\Seeders\PisoSeeder::class,
            \Database\Seeders\AmbienteSeeder::class,
            \Database\Seeders\JornadaFormacionSeeder::class,
        ]);

        $this->service = app(FichaCaracterizacionValidationService::class);
    }

    #[Test]
    public function puede_validar_ficha_completa(): void
    {
        $programa = \App\Models\ProgramaFormacion::factory()->create();
        $ambiente = Ambiente::first();
        $jornada = \App\Models\JornadaFormacion::first();

        $datos = [
            'ficha' => '123456',
            'programa_formacion_id' => $programa->id,
            'ambiente_id' => $ambiente->id ?? null,
            'fecha_inicio' => now()->addMonth()->format('Y-m-d'),
            'fecha_fin' => now()->addMonths(7)->format('Y-m-d'),
            'jornada_id' => $jornada->id ?? null,
        ];

        $resultado = $this->service->validarFichaCompleta($datos);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('valido', $resultado);
        $this->assertArrayHasKey('errores', $resultado);
        $this->assertArrayHasKey('advertencias', $resultado);
    }

    #[Test]
    public function valida_disponibilidad_ambiente(): void
    {
        $ambiente = Ambiente::first();

        if (! $ambiente) {
            $this->markTestSkipped('No hay ambientes disponibles (requiere seeders)');
        }

        $datos = [
            'ambiente_id' => $ambiente->id,
            'fecha_inicio' => now()->addMonth()->format('Y-m-d'),
            'fecha_fin' => now()->addMonths(7)->format('Y-m-d'),
        ];

        $resultado = $this->service->validarDisponibilidadAmbiente(
            $datos['ambiente_id'],
            $datos['fecha_inicio'],
            $datos['fecha_fin']
        );

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('valido', $resultado);
    }
}
