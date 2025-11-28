<?php

namespace Tests\Unit;

use App\Models\ReporteSalidaAutomatica;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReporteSalidaAutomaticaModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);
    }

    #[Test]
    public function tiene_relacion_con_user(): void
    {
        $user = User::factory()->create();
        $reporte = ReporteSalidaAutomatica::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $reporte->user);
        $this->assertEquals($user->id, $reporte->user->id);
    }

    #[Test]
    public function obtiene_detalle_formateado(): void
    {
        $detalle = ['salida1' => 'detalle1', 'salida2' => 'detalle2'];
        $reporte = ReporteSalidaAutomatica::factory()->create([
            'detalle' => $detalle,
        ]);

        $this->assertEquals($detalle, $reporte->detalle_formateado);
    }
}

