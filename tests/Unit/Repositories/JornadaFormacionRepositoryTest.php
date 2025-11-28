<?php

namespace Tests\Unit\Repositories;

use App\Models\JornadaFormacion;
use App\Repositories\JornadaFormacionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JornadaFormacionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected JornadaFormacionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->repository = new JornadaFormacionRepository;
    }

    #[Test]
    public function puede_obtener_jornadas_activas(): void
    {
        JornadaFormacion::factory()->count(3)->create(['status' => true]);
        JornadaFormacion::factory()->count(2)->create(['status' => false]);

        $resultado = $this->repository->obtenerActivas();

        $this->assertCount(3, $resultado);
        $resultado->each(function ($jornada) {
            $this->assertTrue($jornada->status);
        });
    }

    #[Test]
    public function puede_encontrar_jornada_por_nombre(): void
    {
        $jornada = JornadaFormacion::factory()->create(['jornada' => 'DIURNA']);

        $resultado = $this->repository->encontrarPorNombre('DIURNA');

        $this->assertNotNull($resultado);
        $this->assertEquals('DIURNA', $resultado->jornada);
    }
}
