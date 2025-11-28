<?php

namespace Tests\Unit;

use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use App\Repositories\FichaDiasFormacionRepository;
use App\Repositories\FichaRepository;
use App\Repositories\InstructorFichaRepository;
use App\Services\CalendarioService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalendarioServiceTest extends TestCase
{
    use RefreshDatabase;

    private CalendarioService $service;

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

        $this->service = new CalendarioService(
            app(FichaRepository::class),
            app(InstructorFichaRepository::class),
            app(FichaDiasFormacionRepository::class)
        );
    }

    #[Test]
    public function genera_eventos_para_instructor(): void
    {
        $instructor = Instructor::factory()->create();

        $fechaInicio = Carbon::today()->format('Y-m-d');
        $fechaFin = Carbon::today()->addDays(30)->format('Y-m-d');

        $eventos = $this->service->generarEventosInstructor($instructor->id, $fechaInicio, $fechaFin);

        $this->assertIsArray($eventos);
    }

    #[Test]
    public function obtiene_conflictos_de_horario(): void
    {
        $instructor = Instructor::factory()->create();

        $conflictos = $this->service->obtenerConflictosHorario($instructor->id, Carbon::today()->format('Y-m-d'));

        $this->assertIsArray($conflictos);
    }
}

