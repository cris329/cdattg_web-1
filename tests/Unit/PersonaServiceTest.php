<?php

namespace Tests\Unit;

use App\Models\Municipio;
use App\Models\Persona;
use App\Models\User;
use App\Services\PersonaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PersonaService $service;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->service = app(PersonaService::class);
    }

    #[Test]
    public function puede_listar_personas(): void
    {
        Persona::factory()->count(10)->create();

        $resultado = $this->service->listar(5);

        $this->assertCount(5, $resultado->items());
        $this->assertEquals(10, $resultado->total());
    }

    #[Test]
    public function puede_obtener_persona_por_id(): void
    {
        $persona = Persona::factory()->create();

        $resultado = $this->service->obtener($persona->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($persona->id, $resultado->id);
    }

    #[Test]
    public function puede_buscar_persona_por_documento(): void
    {
        $persona = Persona::factory()->create([
            'numero_documento' => '1234567890',
        ]);

        $resultado = $this->service->buscarPorDocumento('1234567890');

        $this->assertNotNull($resultado);
        $this->assertEquals($persona->id, $resultado->id);
    }

    #[Test]
    public function puede_crear_persona(): void
    {
        $municipio = Municipio::first();
        $datos = [
            'numero_documento' => '9876543210',
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'fecha_nacimiento' => '2000-01-01',
            'municipio_id' => $municipio->id,
            'pais_id' => $municipio->departamento->pais_id ?? 1,
            'departamento_id' => $municipio->departamento_id,
        ];

        $persona = $this->service->crear($datos);

        $this->assertInstanceOf(Persona::class, $persona);
        $this->assertDatabaseHas('personas', [
            'numero_documento' => '9876543210',
        ]);
    }

    #[Test]
    public function puede_actualizar_persona(): void
    {
        $persona = Persona::factory()->create();

        $datos = [
            'primer_nombre' => 'Pedro',
        ];

        $resultado = $this->service->actualizar($persona, $datos);

        $this->assertEquals('Pedro', $resultado->primer_nombre);
        $this->assertDatabaseHas('personas', [
            'id' => $persona->id,
            'primer_nombre' => 'Pedro',
        ]);
    }

    #[Test]
    public function no_puede_eliminar_persona_con_dependencias(): void
    {
        $persona = Persona::factory()->create();
        \App\Models\Aprendiz::factory()->create(['persona_id' => $persona->id]);

        $this->expectException(\App\Exceptions\PersonaException::class);
        $this->expectExceptionMessage('No se puede eliminar una persona que es aprendiz o instructor');

        $this->service->eliminar($persona->id);
    }
}
