<?php

namespace Tests\Unit;

use App\Models\Aprendiz;
use App\Services\CarnetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CarnetServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CarnetService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        $this->service = app(CarnetService::class);
    }

    #[Test]
    public function puede_verificar_carnet_valido(): void
    {
        $aprendiz = Aprendiz::factory()->create();

        $qrData = json_encode([
            'tipo' => 'APRENDIZ',
            'id' => $aprendiz->id,
            'documento' => $aprendiz->persona->numero_documento,
        ]);

        $resultado = $this->service->verificarCarnet($qrData);

        $this->assertTrue($resultado['valido']);
    }

    #[Test]
    public function puede_verificar_carnet_invalido(): void
    {
        $qrData = json_encode([
            'tipo' => 'APRENDIZ',
            'id' => 99999,
            'documento' => '9999999999',
        ]);

        $resultado = $this->service->verificarCarnet($qrData);

        $this->assertFalse($resultado['valido']);
    }
}
