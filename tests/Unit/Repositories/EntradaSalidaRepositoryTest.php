<?php

namespace Tests\Unit\Repositories;

use App\Models\EntradaSalida;
use App\Models\Persona;
use App\Repositories\EntradaSalidaRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EntradaSalidaRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EntradaSalidaRepository $repository;

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

        $this->repository = new EntradaSalidaRepository;
    }

    #[Test]
    public function obtiene_registros_por_fecha(): void
    {
        $persona = Persona::factory()->create();
        $fecha = Carbon::today()->format('Y-m-d');

        EntradaSalida::create([
            'fecha' => $fecha,
            'persona_id' => $persona->id,
            'hora_entrada' => '08:00:00',
        ]);

        $resultado = $this->repository->obtenerPorFecha($fecha);

        $this->assertCount(1, $resultado);
        $this->assertEquals($persona->id, $resultado->first()->persona_id);
    }

    #[Test]
    public function retorna_coleccion_vacia_si_no_hay_registros_en_fecha(): void
    {
        $fecha = Carbon::today()->format('Y-m-d');

        $resultado = $this->repository->obtenerPorFecha($fecha);

        $this->assertCount(0, $resultado);
    }

    #[Test]
    public function obtiene_registros_por_persona(): void
    {
        $persona = Persona::factory()->create();

        EntradaSalida::create([
            'fecha' => Carbon::today()->format('Y-m-d'),
            'persona_id' => $persona->id,
            'hora_entrada' => '08:00:00',
        ]);

        $resultado = $this->repository->obtenerPorPersona($persona->id);

        $this->assertCount(1, $resultado);
    }

    #[Test]
    public function obtiene_registros_por_persona_con_rango_fechas(): void
    {
        $persona = Persona::factory()->create();
        $fechaInicio = Carbon::today()->subDays(5)->format('Y-m-d');
        $fechaFin = Carbon::today()->format('Y-m-d');

        EntradaSalida::create([
            'fecha' => Carbon::today()->subDays(3)->format('Y-m-d'),
            'persona_id' => $persona->id,
            'hora_entrada' => '08:00:00',
        ]);

        $resultado = $this->repository->obtenerPorPersona($persona->id, $fechaInicio, $fechaFin);

        $this->assertCount(1, $resultado);
    }

    #[Test]
    public function registra_entrada(): void
    {
        $persona = Persona::factory()->create();

        $entrada = $this->repository->registrarEntrada($persona->id);

        $this->assertDatabaseHas('entrada_salidas', [
            'persona_id' => $persona->id,
            'fecha' => Carbon::today()->format('Y-m-d'),
        ]);
        $this->assertNotNull($entrada->hora_entrada);
    }

    #[Test]
    public function registra_salida(): void
    {
        $persona = Persona::factory()->create();
        $entrada = EntradaSalida::create([
            'fecha' => Carbon::today()->format('Y-m-d'),
            'persona_id' => $persona->id,
            'hora_entrada' => '08:00:00',
        ]);

        $actualizado = $this->repository->registrarSalida($entrada->id);

        $this->assertTrue($actualizado);
        $this->assertDatabaseHas('entrada_salidas', [
            'id' => $entrada->id,
        ]);
    }

    #[Test]
    public function obtiene_registro_abierto(): void
    {
        $persona = Persona::factory()->create();
        $fecha = Carbon::today()->format('Y-m-d');

        EntradaSalida::create([
            'fecha' => $fecha,
            'persona_id' => $persona->id,
            'hora_entrada' => '08:00:00',
            'hora_salida' => null,
        ]);

        $resultado = $this->repository->obtenerRegistroAbierto($persona->id, $fecha);

        $this->assertNotNull($resultado);
        $this->assertNull($resultado->hora_salida);
    }

    #[Test]
    public function retorna_null_si_no_hay_registro_abierto(): void
    {
        $persona = Persona::factory()->create();
        $fecha = Carbon::today()->format('Y-m-d');

        $resultado = $this->repository->obtenerRegistroAbierto($persona->id, $fecha);

        $this->assertNull($resultado);
    }
}
