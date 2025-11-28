<?php

namespace Tests\Unit;

use App\Models\Tema;
use App\Services\TemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TemaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TemaService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->service = app(TemaService::class);
    }

    #[Test]
    public function puede_listar_temas(): void
    {
        Tema::factory()->count(5)->create();

        $resultado = $this->service->listar(10);

        $this->assertGreaterThanOrEqual(5, $resultado->total());
    }

    #[Test]
    public function puede_obtener_tema_con_parametros(): void
    {
        $tema = Tema::first();

        if ($tema) {
            $resultado = $this->service->obtenerConParametros($tema->id);

            $this->assertNotNull($resultado);
        }
    }
}
