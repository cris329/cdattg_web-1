<?php

namespace Tests\Unit;

use App\Models\Instructor;
use App\Models\InstructorFichaCaracterizacion;
use App\Services\InstructorFichaDiasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstructorFichaDiasServiceTest extends TestCase
{
    use RefreshDatabase;

    private InstructorFichaDiasService $service;

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

        $this->service = new InstructorFichaDiasService;
    }

    #[Test]
    public function valida_disponibilidad_instructor(): void
    {
        $instructor = Instructor::factory()->create();
        $instructorFicha = InstructorFichaCaracterizacion::factory()->create([
            'instructor_id' => $instructor->id,
        ]);

        $diasData = [
            [
                'dia_id' => 12,
                'hora_inicio' => '08:00:00',
                'hora_fin' => '12:00:00',
            ],
        ];

        $validacion = $this->service->validarDisponibilidadInstructor($instructorFicha, $diasData);

        $this->assertIsArray($validacion);
        $this->assertArrayHasKey('disponible', $validacion);
    }

    #[Test]
    public function obtiene_dias_asignados(): void
    {
        $instructorFicha = InstructorFichaCaracterizacion::factory()->create();

        $dias = $this->service->obtenerDiasAsignados($instructorFicha->id);

        $this->assertIsArray($dias);
    }

    #[Test]
    public function verifica_disponibilidad(): void
    {
        $instructor = Instructor::factory()->create();

        $disponible = $this->service->estaDisponible($instructor->id, 12, '08:00:00', '12:00:00');

        $this->assertIsBool($disponible);
    }
}

