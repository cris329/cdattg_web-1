<?php

namespace Tests\Feature;

use App\Models\AsignacionInstructor;
use App\Models\Competencia;
use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use App\Models\ResultadosAprendizaje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AsignacionInstructorControllerTest extends TestCase
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
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        // Crear usuario autenticado
        $this->user = User::factory()->create();
    }

    #[Test]
    public function puede_ver_listado_de_asignaciones(): void
    {
        $this->actingAs($this->user);

        AsignacionInstructor::factory()->count(5)->create();

        $response = $this->get(route('asignaciones.instructores.index'));

        $response->assertStatus(200);
        $response->assertViewIs('asignaciones.index');
        $response->assertViewHas('asignaciones');
    }

    #[Test]
    public function puede_ver_formulario_de_creacion(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('asignaciones.instructores.create'));

        $response->assertStatus(200);
        $response->assertViewIs('asignaciones.create');
        $response->assertViewHas(['fichas', 'instructores']);
    }

    #[Test]
    public function puede_crear_asignacion(): void
    {
        $this->actingAs($this->user);

        $ficha = FichaCaracterizacion::factory()->create();
        $instructor = Instructor::factory()->create();
        $competencia = Competencia::factory()->create();
        $resultados = ResultadosAprendizaje::factory()->count(2)->create();

        $response = $this->post(route('asignaciones.instructores.store'), [
            'ficha_id' => $ficha->id,
            'instructor_id' => $instructor->id,
            'competencia_id' => $competencia->id,
            'resultados' => $resultados->pluck('id')->toArray(),
        ]);

        $response->assertRedirect(route('asignaciones.instructores.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('asignaciones_instructores', [
            'ficha_id' => $ficha->id,
            'instructor_id' => $instructor->id,
            'competencia_id' => $competencia->id,
        ]);
    }

    #[Test]
    public function no_puede_crear_asignacion_duplicada(): void
    {
        $this->actingAs($this->user);

        $ficha = FichaCaracterizacion::factory()->create();
        $instructor = Instructor::factory()->create();
        $competencia = Competencia::factory()->create();
        $resultados = ResultadosAprendizaje::factory()->count(2)->create();

        // Crear asignación inicial
        AsignacionInstructor::factory()->create([
            'ficha_id' => $ficha->id,
            'instructor_id' => $instructor->id,
            'competencia_id' => $competencia->id,
        ]);

        // Intentar crear duplicado
        $response = $this->post(route('asignaciones.instructores.store'), [
            'ficha_id' => $ficha->id,
            'instructor_id' => $instructor->id,
            'competencia_id' => $competencia->id,
            'resultados' => $resultados->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    #[Test]
    public function puede_ver_detalles_de_asignacion(): void
    {
        $this->actingAs($this->user);

        $asignacion = AsignacionInstructor::factory()->create();

        $response = $this->get(route('asignaciones.instructores.show', $asignacion->id));

        $response->assertStatus(200);
        $response->assertViewIs('asignaciones.show');
        $response->assertViewHas('asignacion');
    }

    #[Test]
    public function puede_ver_formulario_de_edicion(): void
    {
        $this->actingAs($this->user);

        $asignacion = AsignacionInstructor::factory()->create();

        $response = $this->get(route('asignaciones.instructores.edit', $asignacion->id));

        $response->assertStatus(200);
        $response->assertViewIs('asignaciones.edit');
        $response->assertViewHas(['asignacion', 'fichas', 'instructores']);
    }

    #[Test]
    public function puede_actualizar_asignacion(): void
    {
        $this->actingAs($this->user);

        $asignacion = AsignacionInstructor::factory()->create();
        $nuevaFicha = FichaCaracterizacion::factory()->create();
        $resultados = ResultadosAprendizaje::factory()->count(2)->create();

        $response = $this->put(route('asignaciones.instructores.update', $asignacion->id), [
            'ficha_id' => $nuevaFicha->id,
            'instructor_id' => $asignacion->instructor_id,
            'competencia_id' => $asignacion->competencia_id,
            'resultados' => $resultados->pluck('id')->toArray(),
        ]);

        $response->assertRedirect(route('asignaciones.instructores.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('asignaciones_instructores', [
            'id' => $asignacion->id,
            'ficha_id' => $nuevaFicha->id,
        ]);
    }

    #[Test]
    public function puede_eliminar_asignacion(): void
    {
        $this->actingAs($this->user);

        $asignacion = AsignacionInstructor::factory()->create();

        $response = $this->delete(route('asignaciones.instructores.destroy', $asignacion->id));

        $response->assertRedirect(route('asignaciones.instructores.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('asignaciones_instructores', [
            'id' => $asignacion->id,
        ]);
    }

    #[Test]
    public function requiere_autenticacion_para_ver_asignaciones(): void
    {
        $response = $this->get(route('asignaciones.instructores.index'));

        $response->assertRedirect(route('login'));
    }
}
