<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Bloque;
use App\Models\Sede;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class BloqueModelTest extends TestCase
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
    public function puede_crear_bloque(): void
    {
        $sede = Sede::factory()->create();

        $bloque = Bloque::factory()->create([
            'sede_id' => $sede->id,
            'bloque' => 'Bloque A',
        ]);

        $this->assertInstanceOf(Bloque::class, $bloque);
        $this->assertEquals('BLOQUE A', $bloque->bloque);
    }

    #[Test]
    public function tiene_relacion_con_sede(): void
    {
        $sede = Sede::factory()->create();
        $bloque = Bloque::factory()->create(['sede_id' => $sede->id]);

        $this->assertNotNull($bloque->sede);
        $this->assertEquals($sede->id, $bloque->sede->id);
    }
}

