<?php

namespace Tests\Feature;

use App\Models\Parametro;
use App\Models\ProgramaFormacion;
use App\Models\RedConocimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProgramaFormacionControllerTest extends TestCase
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
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        // Crear permisos necesarios
        Permission::firstOrCreate(['name' => 'programa.index']);
        Permission::firstOrCreate(['name' => 'programa.create']);
        Permission::firstOrCreate(['name' => 'programa.edit']);
        Permission::firstOrCreate(['name' => 'programa.delete']);
        Permission::firstOrCreate(['name' => 'programa.search']);
        Permission::firstOrCreate(['name' => 'programa.show']);

        // Crear usuario con permisos usando factory
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('programa.index');
    }

    #[Test]
    public function puede_ver_listado_de_programas(): void
    {
        $this->actingAs($this->user);

        ProgramaFormacion::factory()->count(5)->create();

        $response = $this->get(route('programa.index'));

        $response->assertStatus(200);
        $response->assertViewIs('programas.index');
        $response->assertViewHas('programas');
    }

    #[Test]
    public function puede_buscar_programas(): void
    {
        $this->user->givePermissionTo('programa.search');
        $this->actingAs($this->user);

        $programa = ProgramaFormacion::factory()->create(['nombre' => 'Programa Test']);

        $response = $this->get(route('programa.search', ['search' => 'Test']));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_ver_formulario_de_creacion(): void
    {
        $this->user->givePermissionTo('programa.create');
        $this->actingAs($this->user);

        $response = $this->get(route('programa.create'));

        $response->assertStatus(200);
        $response->assertViewIs('programas.create');
    }

    #[Test]
    public function puede_crear_programa(): void
    {
        $this->user->givePermissionTo('programa.create');
        $this->actingAs($this->user);

        $redConocimiento = RedConocimiento::first() ?? RedConocimiento::factory()->create();
        $nivelFormacion = Parametro::whereHas('temas', function ($query) {
            $query->where('temas.id', 6);
        })->first();

        if (! $nivelFormacion) {
            $this->markTestSkipped('No hay niveles de formación disponibles (requiere seeders)');
        }

        $response = $this->post(route('programa.store'), [
            'codigo' => 'PROG-'.$this->faker->unique()->numerify('####'),
            'nombre' => 'Programa de Prueba '.$this->faker->word(),
            'red_conocimiento_id' => $redConocimiento->id,
            'nivel_formacion_id' => $nivelFormacion->id,
            'horas_totales' => 2400,
            'horas_etapa_lectiva' => 1800,
            'horas_etapa_productiva' => 600,
        ]);

        $response->assertRedirect(route('programa.index'));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function no_puede_crear_programa_sin_permiso(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('programa.store'), []);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_ver_detalles_de_programa(): void
    {
        $this->user->givePermissionTo('programa.show');
        $this->actingAs($this->user);

        $programa = ProgramaFormacion::factory()->create();

        $response = $this->get(route('programa.show', $programa->id));

        $response->assertStatus(200);
        $response->assertViewHas('programa');
    }

    #[Test]
    public function puede_ver_formulario_de_edicion(): void
    {
        $this->user->givePermissionTo('programa.edit');
        $this->actingAs($this->user);

        $programa = ProgramaFormacion::factory()->create();

        $response = $this->get(route('programa.edit', $programa->id));

        $response->assertStatus(200);
        $response->assertViewHas('programa');
    }

    #[Test]
    public function puede_actualizar_programa(): void
    {
        $this->user->givePermissionTo('programa.edit');
        $this->actingAs($this->user);

        $programa = ProgramaFormacion::factory()->create();

        $response = $this->put(route('programa.update', $programa->id), [
            'codigo' => $programa->codigo,
            'nombre' => 'Programa Actualizado',
            'red_conocimiento_id' => $programa->red_conocimiento_id,
            'nivel_formacion_id' => $programa->nivel_formacion_id,
            'horas_totales' => $programa->horas_totales,
            'horas_etapa_lectiva' => $programa->horas_etapa_lectiva,
            'horas_etapa_productiva' => $programa->horas_etapa_productiva,
        ]);

        $response->assertRedirect(route('programa.index'));
        $this->assertDatabaseHas('programas_formacion', [
            'id' => $programa->id,
            'nombre' => 'Programa Actualizado',
        ]);
    }

    #[Test]
    public function puede_eliminar_programa(): void
    {
        $this->user->givePermissionTo('programa.delete');
        $this->actingAs($this->user);

        $programa = ProgramaFormacion::factory()->create();

        $response = $this->delete(route('programa.destroy', $programa->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('programas_formacion', [
            'id' => $programa->id,
        ]);
    }

    #[Test]
    public function requiere_autenticacion_para_ver_programas(): void
    {
        $response = $this->get(route('programa.index'));

        $response->assertRedirect(route('login'));
    }
}
