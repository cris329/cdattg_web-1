<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CentroFormacion;
use App\Models\Regional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class CentroFormacionModelTest extends TestCase
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
    public function puede_crear_centro_formacion(): void
    {
        $regional = Regional::first();

        if ($regional) {
            $centro = CentroFormacion::factory()->create([
                'regional_id' => $regional->id,
            ]);

            $this->assertInstanceOf(CentroFormacion::class, $centro);
        }
    }

    #[Test]
    public function tiene_relacion_con_regional(): void
    {
        $regional = Regional::first();

        if ($regional) {
            $centro = CentroFormacion::factory()->create([
                'regional_id' => $regional->id,
            ]);

            $this->assertNotNull($centro->regional);
            $this->assertEquals($regional->id, $centro->regional->id);
        }
    }
}

