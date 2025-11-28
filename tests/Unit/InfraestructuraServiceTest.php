<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\InfraestructuraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class InfraestructuraServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InfraestructuraService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);

        $this->service = app(InfraestructuraService::class);
    }

    #[Test]
    public function servicio_existe(): void
    {
        $this->assertInstanceOf(InfraestructuraService::class, $this->service);
    }

    #[Test]
    public function puede_obtener_estructura_completa(): void
    {
        $resultado = $this->service->obtenerEstructuraCompleta();

        $this->assertIsArray($resultado);
    }
}

