<?php

namespace Tests\Unit;

use App\Models\EntradaSalida;
use App\Models\Persona;
use App\Services\EntradaSalidaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EntradaSalidaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EntradaSalidaService $service;

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

        $this->service = app(EntradaSalidaService::class);
    }

    #[Test]
    public function servicio_existe(): void
    {
        $this->assertInstanceOf(EntradaSalidaService::class, $this->service);
    }

    #[Test]
    public function puede_obtener_registros_por_fecha(): void
    {
        $persona = Persona::factory()->create();
        EntradaSalida::factory()->create([
            'persona_id' => $persona->id,
            'fecha' => now()->format('Y-m-d'),
        ]);

        $resultado = $this->service->obtenerPorFecha(now()->format('Y-m-d'));

        $this->assertIsIterable($resultado);
    }
}
