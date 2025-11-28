<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\RedConocimiento;
use App\Models\Regional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class RedConocimientoModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);
    }

    #[Test]
    public function puede_crear_red_conocimiento(): void
    {
        $regional = Regional::first();

        if ($regional) {
            $red = RedConocimiento::factory()->create([
                'regionals_id' => $regional->id,
                'nombre' => 'Tecnologías de la Información',
            ]);

            $this->assertInstanceOf(RedConocimiento::class, $red);
            $this->assertEquals('TECNOLOGÍAS DE LA INFORMACIÓN', $red->nombre);
        }
    }

    #[Test]
    public function tiene_relacion_con_regional(): void
    {
        $regional = Regional::first();

        if ($regional) {
            $red = RedConocimiento::factory()->create([
                'regionals_id' => $regional->id,
            ]);

            $this->assertNotNull($red->regional);
            $this->assertEquals($regional->id, $red->regional->id);
        }
    }
}

