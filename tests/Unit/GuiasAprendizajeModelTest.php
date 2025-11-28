<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\GuiasAprendizaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class GuiasAprendizajeModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);
    }

    #[Test]
    public function puede_crear_guia_aprendizaje(): void
    {
        $guia = GuiasAprendizaje::factory()->create([
            'codigo' => 'GA001',
            'nombre' => 'Guía Test',
        ]);

        $this->assertInstanceOf(GuiasAprendizaje::class, $guia);
        $this->assertEquals('GA001', $guia->codigo);
        $this->assertEquals('GUÍA TEST', $guia->nombre);
    }
}

