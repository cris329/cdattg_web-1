<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Sede;
use App\Models\Municipio;
use App\Models\Regional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class SedeModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);
    }

    #[Test]
    public function puede_crear_sede(): void
    {
        $municipio = Municipio::first();
        $regional = Regional::first();

        if ($municipio && $regional) {
            $sede = Sede::factory()->create([
                'municipio_id' => $municipio->id,
                'regional_id' => $regional->id,
                'sede' => 'Sede Principal',
            ]);

            $this->assertInstanceOf(Sede::class, $sede);
            $this->assertEquals('SEDE PRINCIPAL', $sede->sede);
        }
    }

    #[Test]
    public function tiene_relacion_con_municipio(): void
    {
        $municipio = Municipio::first();
        $regional = Regional::first();

        if ($municipio && $regional) {
            $sede = Sede::factory()->create([
                'municipio_id' => $municipio->id,
                'regional_id' => $regional->id,
            ]);

            $this->assertNotNull($sede->municipio);
            $this->assertEquals($municipio->id, $sede->municipio->id);
        }
    }
}

