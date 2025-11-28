<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Regional;
use App\Models\Departamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class RegionalModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
        ]);
    }

    #[Test]
    public function puede_crear_regional(): void
    {
        $departamento = Departamento::first();

        if ($departamento) {
            $regional = Regional::factory()->create([
                'departamento_id' => $departamento->id,
                'nombre' => 'Regional Centro',
            ]);

            $this->assertInstanceOf(Regional::class, $regional);
            $this->assertEquals('REGIONAL CENTRO', $regional->nombre);
        }
    }

    #[Test]
    public function tiene_relacion_con_departamento(): void
    {
        $departamento = Departamento::first();

        if ($departamento) {
            $regional = Regional::factory()->create([
                'departamento_id' => $departamento->id,
            ]);

            $this->assertNotNull($regional->departamento);
            $this->assertEquals($departamento->id, $regional->departamento->id);
        }
    }
}

