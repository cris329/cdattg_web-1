<?php

namespace Tests\Unit;

use App\Models\PersonaIngresoSalida;
use App\Services\PersonaIngresoSalidaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonaIngresoSalidaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PersonaIngresoSalidaService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->service = app(PersonaIngresoSalidaService::class);
    }

    #[Test]
    public function puede_instanciar_servicio(): void
    {
        $this->assertInstanceOf(PersonaIngresoSalidaService::class, $this->service);
    }

    #[Test]
    public function obtiene_tipos_persona(): void
    {
        $tipos = $this->service->obtenerTiposPersona();

        $this->assertIsArray($tipos);
    }

    #[Test]
    public function obtiene_configuracion_tipos_persona(): void
    {
        $configuracion = $this->service->obtenerConfiguracionTiposPersona();

        $this->assertIsArray($configuracion);
    }
}


