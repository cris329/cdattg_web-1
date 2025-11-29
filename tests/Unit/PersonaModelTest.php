<?php

namespace Tests\Unit;

use App\Models\Aprendiz;
use App\Models\Instructor;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonaModelTest extends TestCase
{
    use RefreshDatabase;

    protected Persona $persona;

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

        $this->persona = Persona::factory()->create();
    }

    #[Test]
    public function tiene_relacion_con_usuario(): void
    {
        $user = User::factory()->create(['persona_id' => $this->persona->id]);

        $this->assertInstanceOf(User::class, $this->persona->user);
        $this->assertEquals($user->id, $this->persona->user->id);
    }

    #[Test]
    public function tiene_relacion_con_instructor(): void
    {
        $instructor = Instructor::factory()->create(['persona_id' => $this->persona->id]);

        $this->assertInstanceOf(Instructor::class, $this->persona->instructor);
        $this->assertEquals($instructor->id, $this->persona->instructor->id);
    }

    #[Test]
    public function tiene_relacion_con_aprendiz(): void
    {
        $aprendiz = Aprendiz::factory()->create(['persona_id' => $this->persona->id]);

        $this->assertInstanceOf(Aprendiz::class, $this->persona->aprendiz);
        $this->assertEquals($aprendiz->id, $this->persona->aprendiz->id);
    }

    #[Test]
    public function puede_verificar_si_es_aprendiz(): void
    {
        $this->assertFalse($this->persona->esAprendiz());

        Aprendiz::factory()->create(['persona_id' => $this->persona->id]);

        $this->assertTrue($this->persona->fresh()->esAprendiz());
    }

    #[Test]
    public function puede_verificar_si_es_aprendiz_activo(): void
    {
        Aprendiz::factory()->create([
            'persona_id' => $this->persona->id,
            'estado' => 1,
        ]);

        $this->assertTrue($this->persona->fresh()->esAprendizActivo());
    }

    #[Test]
    public function tiene_relacion_con_municipio(): void
    {
        $municipio = \App\Models\Municipio::first();
        $this->persona->update(['municipio_id' => $municipio->id]);

        $this->assertInstanceOf(\App\Models\Municipio::class, $this->persona->municipio);
        $this->assertEquals($municipio->id, $this->persona->municipio->id);
    }

    #[Test]
    public function tiene_relacion_con_tipo_documento(): void
    {
        $tipoDocumento = \App\Models\Parametro::first();
        $this->persona->update(['tipo_documento' => $tipoDocumento->id]);

        $this->assertInstanceOf(\App\Models\Parametro::class, $this->persona->tipoDocumento);
    }

    #[Test]
    public function normaliza_nombres_a_mayusculas_al_guardar(): void
    {
        $persona = Persona::factory()->create([
            'primer_nombre' => 'juan',
            'primer_apellido' => 'pérez',
        ]);

        $this->assertEquals('JUAN', $persona->primer_nombre);
        $this->assertEquals('PÉREZ', $persona->primer_apellido);
    }
}
