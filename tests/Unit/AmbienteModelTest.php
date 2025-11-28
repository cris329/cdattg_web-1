<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Ambiente;
use App\Models\Piso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AmbienteModelTest extends TestCase
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
    public function puede_crear_ambiente(): void
    {
        $piso = Piso::factory()->create();

        $ambiente = Ambiente::factory()->create([
            'piso_id' => $piso->id,
            'title' => 'Sala 101',
        ]);

        $this->assertInstanceOf(Ambiente::class, $ambiente);
        $this->assertEquals('SALA 101', $ambiente->title);
    }

    #[Test]
    public function tiene_relacion_con_piso(): void
    {
        $piso = Piso::factory()->create();
        $ambiente = Ambiente::factory()->create(['piso_id' => $piso->id]);

        $this->assertNotNull($ambiente->piso);
        $this->assertEquals($piso->id, $ambiente->piso->id);
    }
}

