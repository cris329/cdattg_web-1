<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Piso;
use App\Models\Bloque;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class PisoModelTest extends TestCase
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
    public function puede_crear_piso(): void
    {
        $bloque = Bloque::factory()->create();

        $piso = Piso::factory()->create([
            'bloque_id' => $bloque->id,
            'piso' => 'Piso 2',
        ]);

        $this->assertInstanceOf(Piso::class, $piso);
        $this->assertEquals('PISO 2', $piso->piso);
    }

    #[Test]
    public function tiene_relacion_con_bloque(): void
    {
        $bloque = Bloque::factory()->create();
        $piso = Piso::factory()->create(['bloque_id' => $bloque->id]);

        $this->assertNotNull($piso->bloque);
        $this->assertEquals($bloque->id, $piso->bloque->id);
    }
}

