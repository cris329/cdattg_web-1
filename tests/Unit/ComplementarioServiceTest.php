<?php

namespace Tests\Unit;

use App\Models\ComplementarioOfertado;
use App\Repositories\TemaRepository;
use App\Services\ComplementarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ComplementarioServiceTest extends TestCase
{
    use RefreshDatabase;

    private ComplementarioService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->service = new ComplementarioService(
            app(TemaRepository::class)
        );
    }

    #[Test]
    public function obtiene_icono_para_programa(): void
    {
        $icono = $this->service->getIconoForPrograma('Auxiliar de Cocina');

        $this->assertEquals('fas fa-utensils', $icono);
    }

    #[Test]
    public function obtiene_badge_class_para_estado(): void
    {
        $badge = $this->service->getBadgeClassForEstado(1);

        $this->assertEquals('bg-success', $badge);
    }

    #[Test]
    public function obtiene_estado_label(): void
    {
        $label = $this->service->getEstadoLabel(1);

        $this->assertEquals('Con Oferta', $label);
    }

    #[Test]
    public function verifica_inscripcion_existente(): void
    {
        $personaId = 1;
        $programaId = 1;

        $existe = $this->service->verificarInscripcionExistente($personaId, $programaId);

        $this->assertIsBool($existe);
    }

    #[Test]
    public function obtiene_programas_con_relaciones(): void
    {
        $programas = $this->service->obtenerProgramas();

        $this->assertIsIterable($programas);
    }
}

