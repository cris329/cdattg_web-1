<?php

namespace Tests\Feature;

use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Pais;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PersonaControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Seeders base para datos realistas
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\TemaSeeder::class,
        ]);

        // Crear permisos necesarios
        Permission::firstOrCreate(['name' => 'VER PERSONA']);
        Permission::firstOrCreate(['name' => 'CREAR PERSONA']);
        Permission::firstOrCreate(['name' => 'EDITAR PERSONA']);
        Permission::firstOrCreate(['name' => 'ELIMINAR PERSONA']);
        Permission::firstOrCreate(['name' => 'VER PERFIL']);

        // Crear usuario con permisos usando factory
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER PERSONA');
    }

    #[Test]
    public function puede_ver_listado_de_personas(): void
    {
        $this->actingAs($this->user);

        // Crear personas usando factories (usan datos de seeders)
        Persona::factory()->count(5)->create();

        $response = $this->get(route('personas.index'));

        $response->assertStatus(200);
        $response->assertViewIs('personas.index');
    }

    #[Test]
    public function puede_buscar_personas_por_nombre(): void
    {
        $this->actingAs($this->user);

        $persona = Persona::factory()->create([
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
        ]);

        $response = $this->get(route('personas.index', ['search' => 'Juan']));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_ver_formulario_de_creacion(): void
    {
        $this->user->givePermissionTo('CREAR PERSONA');
        $this->actingAs($this->user);

        $response = $this->get(route('personas.create'));

        $response->assertStatus(200);
        $response->assertViewIs('personas.create');
    }

    #[Test]
    public function puede_crear_persona(): void
    {
        $this->user->givePermissionTo('CREAR PERSONA');
        $this->actingAs($this->user);

        // Obtener datos reales de seeders
        $municipio = Municipio::first();
        $pais = Pais::first();
        $departamento = Departamento::first();

        $response = $this->post(route('personas.store'), [
            'numero_documento' => $this->faker->unique()->numerify('##########'),
            'primer_nombre' => $this->faker->firstName(),
            'primer_apellido' => $this->faker->lastName(),
            'fecha_nacimiento' => $this->faker->date('Y-m-d', '-20 years'),
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'celular' => $this->faker->numerify('3##########'),
            'email' => $this->faker->unique()->email(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    #[Test]
    public function no_puede_crear_persona_sin_permiso(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('personas.store'), []);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_ver_detalles_de_persona(): void
    {
        $this->actingAs($this->user);

        $persona = Persona::factory()->create();

        $response = $this->get(route('personas.show', $persona->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_ver_formulario_de_edicion(): void
    {
        $this->user->givePermissionTo('EDITAR PERSONA');
        $this->actingAs($this->user);

        $persona = Persona::factory()->create();

        $response = $this->get(route('personas.edit', $persona->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_actualizar_persona(): void
    {
        $this->user->givePermissionTo('EDITAR PERSONA');
        $this->actingAs($this->user);

        $persona = Persona::factory()->create();

        $response = $this->put(route('personas.update', $persona->id), [
            'numero_documento' => $persona->numero_documento,
            'primer_nombre' => 'Pedro',
            'primer_apellido' => $persona->primer_apellido,
            'fecha_nacimiento' => $persona->fecha_nacimiento->format('Y-m-d'),
            'pais_id' => $persona->pais_id,
            'departamento_id' => $persona->departamento_id,
            'municipio_id' => $persona->municipio_id,
            'celular' => $persona->celular,
            'email' => $persona->email,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('personas', [
            'id' => $persona->id,
            'primer_nombre' => 'Pedro',
        ]);
    }

    #[Test]
    public function puede_eliminar_persona(): void
    {
        $this->user->givePermissionTo('ELIMINAR PERSONA');
        $this->actingAs($this->user);

        $persona = Persona::factory()->create();

        $response = $this->delete(route('personas.destroy', $persona->id));

        $response->assertRedirect();
        $this->assertSoftDeleted('personas', [
            'id' => $persona->id,
        ]);
    }

    #[Test]
    public function puede_ver_su_perfil(): void
    {
        $this->user->givePermissionTo('VER PERFIL');
        $this->actingAs($this->user);

        $response = $this->get(route('personas.mi-perfil'));

        $response->assertRedirect();
    }

    #[Test]
    public function requiere_autenticacion_para_ver_personas(): void
    {
        $response = $this->get(route('personas.index'));

        $response->assertRedirect(route('login'));
    }
}
