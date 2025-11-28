<?php

namespace Tests\Unit;

use App\Models\Instructor;
use App\Services\InstructorBusinessRulesService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstructorBusinessRulesServiceTest extends TestCase
{
    use RefreshDatabase;

    private InstructorBusinessRulesService $service;

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

        $this->service = new InstructorBusinessRulesService;
    }

    #[Test]
    public function verifica_disponibilidad_instructor(): void
    {
        $instructor = Instructor::factory()->create();
        $datosFicha = [
            'fecha_inicio' => Carbon::today()->format('Y-m-d'),
            'fecha_fin' => Carbon::today()->addMonths(6)->format('Y-m-d'),
        ];

        $resultado = $this->service->verificarDisponibilidad($instructor, $datosFicha);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('disponible', $resultado);
    }

    #[Test]
    public function verifica_si_excede_limite_fichas_activas(): void
    {
        $instructor = Instructor::factory()->create();

        $excede = $this->service->excedeLimiteFichasActivas($instructor);

        $this->assertIsBool($excede);
    }

    #[Test]
    public function cuenta_fichas_activas(): void
    {
        $instructor = Instructor::factory()->create();

        $count = $this->service->contarFichasActivas($instructor);

        $this->assertIsInt($count);
    }

    #[Test]
    public function cuenta_fichas_finalizadas(): void
    {
        $instructor = Instructor::factory()->create();

        $count = $this->service->contarFichasFinalizadas($instructor);

        $this->assertIsInt($count);
    }

    #[Test]
    public function valida_experiencia_minima(): void
    {
        $instructor = Instructor::factory()->create(['anos_experiencia' => 2]);

        $valido = $this->service->validarExperienciaMinima($instructor);

        $this->assertTrue($valido);
    }

    #[Test]
    public function obtiene_resumen_fichas(): void
    {
        $instructor = Instructor::factory()->create();

        $resumen = $this->service->obtenerResumenFichas($instructor);

        $this->assertIsArray($resumen);
        $this->assertArrayHasKey('total', $resumen);
        $this->assertArrayHasKey('activas', $resumen);
    }
}

