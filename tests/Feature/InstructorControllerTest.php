<?php

namespace Tests\Feature;

use App\Models\Instructor;
use App\Models\Persona;
use App\Models\Regional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InstructorControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear permisos necesarios
        Permission::firstOrCreate(['name' => 'VER INSTRUCTOR']);
        Permission::firstOrCreate(['name' => 'CREAR INSTRUCTOR']);
        Permission::firstOrCreate(['name' => 'EDITAR INSTRUCTOR']);
        Permission::firstOrCreate(['name' => 'ELIMINAR INSTRUCTOR']);
        Permission::firstOrCreate(['name' => 'CAMBIAR ESTADO INSTRUCTOR']);

        // Crear usuario con permisos
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER INSTRUCTOR');
    }

    #[Test]
    public function puede_ver_listado_de_instructores(): void
    {
        $this->actingAs($this->user);

        Instructor::factory()->count(5)->create();

        $response = $this->get(route('instructor.index'));

        $response->assertStatus(200);
        $response->assertViewIs('Instructores.index');
        $response->assertViewHas('instructores');
        $response->assertViewHas('regionales');
        $response->assertViewHas('especialidades');
    }

    #[Test]
    public function puede_buscar_instructores_por_nombre(): void
    {
        $this->actingAs($this->user);

        $persona = Persona::factory()->create([
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
        ]);

        Instructor::factory()->create(['persona_id' => $persona->id]);

        $response = $this->get(route('instructor.index', ['search' => 'Juan']));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_filtrar_instructores_por_estado(): void
    {
        $this->actingAs($this->user);

        Instructor::factory()->create(['status' => true]);
        Instructor::factory()->create(['status' => false]);

        $response = $this->get(route('instructor.index', ['estado' => 'activos']));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_filtrar_instructores_por_regional(): void
    {
        $this->actingAs($this->user);

        $regional = Regional::factory()->create();
        Instructor::factory()->create(['regional_id' => $regional->id]);

        $response = $this->get(route('instructor.index', ['regional' => $regional->id]));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_ver_formulario_de_creacion(): void
    {
        $this->user->givePermissionTo('CREAR INSTRUCTOR');
        $this->actingAs($this->user);

        $response = $this->get(route('instructor.create'));

        $response->assertStatus(200);
        $response->assertViewIs('Instructores.create');
    }

    #[Test]
    public function puede_crear_instructor(): void
    {
        $this->user->givePermissionTo('CREAR INSTRUCTOR');
        $this->actingAs($this->user);

        $persona = Persona::factory()->create();
        $user = User::factory()->forPersona($persona)->create();
        $regional = Regional::factory()->create();

        $response = $this->post(route('instructor.store'), [
            'persona_id' => $persona->id,
            'regional_id' => $regional->id,
            'anos_experiencia' => 5,
            'status' => true,
        ]);

        $response->assertRedirect(route('instructor.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('instructors', [
            'persona_id' => $persona->id,
            'regional_id' => $regional->id,
        ]);
    }

    #[Test]
    public function no_puede_crear_instructor_sin_permiso(): void
    {
        $this->actingAs($this->user);

        $persona = Persona::factory()->create();
        $regional = Regional::factory()->create();

        $response = $this->post(route('instructor.store'), [
            'persona_id' => $persona->id,
            'regional_id' => $regional->id,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_ver_detalles_de_instructor(): void
    {
        $this->actingAs($this->user);

        $instructor = Instructor::factory()->create();

        $response = $this->get(route('instructor.show', $instructor->id));

        $response->assertStatus(200);
        $response->assertViewIs('Instructores.show');
        $response->assertViewHas('instructor');
    }

    #[Test]
    public function puede_ver_formulario_de_edicion(): void
    {
        $this->user->givePermissionTo('EDITAR INSTRUCTOR');
        $this->actingAs($this->user);

        $instructor = Instructor::factory()->create();

        $response = $this->get(route('instructor.edit', $instructor->id));

        $response->assertStatus(200);
        $response->assertViewIs('Instructores.edit');
    }

    #[Test]
    public function puede_actualizar_instructor(): void
    {
        $this->user->givePermissionTo('EDITAR INSTRUCTOR');
        $this->actingAs($this->user);

        $instructor = Instructor::factory()->create();
        $nuevaRegional = Regional::factory()->create();

        $persona = $instructor->persona;
        $user = User::where('persona_id', $persona->id)->first();

        $response = $this->put(route('instructor.update', $instructor->id), [
            'tipo_documento' => $persona->tipo_documento,
            'numero_documento' => $persona->numero_documento,
            'primer_nombre' => 'Pedro',
            'primer_apellido' => $persona->primer_apellido,
            'fecha_de_nacimiento' => $persona->fecha_nacimiento,
            'genero' => $persona->genero,
            'email' => $persona->email,
            'regional_id' => $nuevaRegional->id,
        ]);

        $response->assertRedirect(route('instructor.show', $instructor->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('instructors', [
            'id' => $instructor->id,
            'regional_id' => $nuevaRegional->id,
        ]);
    }

    #[Test]
    public function puede_eliminar_instructor(): void
    {
        $this->user->givePermissionTo('ELIMINAR INSTRUCTOR');
        $this->actingAs($this->user);

        $instructor = Instructor::factory()->create();

        $response = $this->delete(route('instructor.destroy', $instructor->id));

        $response->assertRedirect(route('instructor.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('instructors', [
            'id' => $instructor->id,
        ]);
    }

    #[Test]
    public function puede_buscar_instructores_via_ajax(): void
    {
        $this->actingAs($this->user);

        $persona = Persona::factory()->create(['primer_nombre' => 'María']);
        Instructor::factory()->create(['persona_id' => $persona->id]);

        $response = $this->getJson(route('instructor.search', [
            'search' => 'María',
            'estado' => 'todos',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    #[Test]
    public function requiere_autenticacion_para_ver_instructores(): void
    {
        $response = $this->get(route('instructor.index'));

        $response->assertRedirect(route('login'));
    }
}
