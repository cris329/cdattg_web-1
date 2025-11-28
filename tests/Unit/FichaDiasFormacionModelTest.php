<?php

namespace Tests\Unit;

use App\Models\FichaCaracterizacion;
use App\Models\FichaDiasFormacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FichaDiasFormacionModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);
    }

    #[Test]
    public function tiene_relacion_con_ficha(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $fichaDias = FichaDiasFormacion::factory()->create(['ficha_id' => $ficha->id]);

        $this->assertInstanceOf(FichaCaracterizacion::class, $fichaDias->ficha);
        $this->assertEquals($ficha->id, $fichaDias->ficha->id);
    }

    #[Test]
    public function tiene_relacion_con_dia(): void
    {
        $parametro = \App\Models\Parametro::first();
        $fichaDias = FichaDiasFormacion::factory()->create(['dia_id' => $parametro->id]);

        $this->assertInstanceOf(\App\Models\Parametro::class, $fichaDias->dia);
    }

    #[Test]
    public function calcula_horas_dia(): void
    {
        $fichaDias = FichaDiasFormacion::factory()->create([
            'hora_inicio' => '08:00:00',
            'hora_fin' => '12:00:00',
        ]);

        $horas = $fichaDias->calcularHorasDia();

        $this->assertIsString($horas);
        $this->assertStringContainsString('horas', $horas);
    }
}

