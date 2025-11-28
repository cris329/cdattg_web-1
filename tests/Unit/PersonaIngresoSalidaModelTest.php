<?php

namespace Tests\Unit;

use App\Models\Ambiente;
use App\Models\FichaCaracterizacion;
use App\Models\Persona;
use App\Models\PersonaIngresoSalida;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonaIngresoSalidaModelTest extends TestCase
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
    public function tiene_relacion_con_persona(): void
    {
        $persona = Persona::factory()->create();
        $registro = PersonaIngresoSalida::factory()->create([
            'persona_id' => $persona->id,
        ]);

        $this->assertInstanceOf(Persona::class, $registro->persona);
        $this->assertEquals($persona->id, $registro->persona->id);
    }

    #[Test]
    public function tiene_relacion_con_sede(): void
    {
        $sede = Sede::factory()->create();
        $registro = PersonaIngresoSalida::factory()->create([
            'sede_id' => $sede->id,
        ]);

        $this->assertInstanceOf(Sede::class, $registro->sede);
    }

    #[Test]
    public function tiene_relacion_con_ambiente(): void
    {
        $ambiente = Ambiente::factory()->create();
        $registro = PersonaIngresoSalida::factory()->create([
            'ambiente_id' => $ambiente->id,
        ]);

        $this->assertInstanceOf(Ambiente::class, $registro->ambiente);
    }

    #[Test]
    public function tiene_relacion_con_ficha_caracterizacion(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $registro = PersonaIngresoSalida::factory()->create([
            'ficha_caracterizacion_id' => $ficha->id,
        ]);

        $this->assertInstanceOf(FichaCaracterizacion::class, $registro->fichaCaracterizacion);
    }

    #[Test]
    public function verifica_si_esta_dentro(): void
    {
        $registro = PersonaIngresoSalida::factory()->create([
            'timestamp_entrada' => now(),
            'timestamp_salida' => null,
        ]);

        $this->assertTrue($registro->estaDentro());
    }

    #[Test]
    public function scope_dentro_filtra_personas_dentro(): void
    {
        PersonaIngresoSalida::factory()->create([
            'timestamp_entrada' => now(),
            'timestamp_salida' => null,
        ]);
        PersonaIngresoSalida::factory()->create([
            'timestamp_entrada' => now()->subHours(2),
            'timestamp_salida' => now(),
        ]);

        $dentro = PersonaIngresoSalida::dentro()->get();

        $this->assertCount(1, $dentro);
    }
}

