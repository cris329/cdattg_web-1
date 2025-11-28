<?php

namespace Tests\Feature;

use App\Models\Ambiente;
use App\Models\FichaCaracterizacion;
use App\Models\JornadaFormacion;
use App\Models\ProgramaFormacion;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FichaCaracterizacionControllerTest extends TestCase
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
            \Database\Seeders\RegionalSeeder::class,
            \Database\Seeders\SedeSeeder::class,
            \Database\Seeders\CentroFormacionSeeder::class,
            \Database\Seeders\BloqueSeeder::class,
            \Database\Seeders\PisoSeeder::class,
            \Database\Seeders\AmbienteSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
            \Database\Seeders\JornadaFormacionSeeder::class,
        ]);

        // Crear permisos necesarios
        Permission::firstOrCreate(['name' => 'VER FICHA CARACTERIZACION']);
        Permission::firstOrCreate(['name' => 'CREAR PROGRAMA DE CARACTERIZACION']);
        Permission::firstOrCreate(['name' => 'EDITAR FICHA CARACTERIZACION']);
        Permission::firstOrCreate(['name' => 'ELIMINAR FICHA CARACTERIZACION']);

        // Crear usuario con permisos usando factory
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER FICHA CARACTERIZACION');
    }

    #[Test]
    public function puede_ver_listado_de_fichas(): void
    {
        $this->actingAs($this->user);

        FichaCaracterizacion::factory()->count(5)->create();

        $response = $this->get(route('fichaCaracterizacion.index'));

        $response->assertStatus(200);
        $response->assertViewIs('fichas.index');
        $response->assertViewHas('fichas');
    }

    #[Test]
    public function puede_buscar_fichas(): void
    {
        $this->actingAs($this->user);

        $ficha = FichaCaracterizacion::factory()->create(['ficha' => '123456']);

        $response = $this->get(route('fichaCaracterizacion.index', ['search' => '123456']));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_ver_formulario_de_creacion(): void
    {
        $this->user->givePermissionTo('CREAR PROGRAMA DE CARACTERIZACION');
        $this->actingAs($this->user);

        $response = $this->get(route('fichaCaracterizacion.create'));

        $response->assertStatus(200);
        $response->assertViewIs('fichas.create');
    }

    #[Test]
    public function puede_crear_ficha(): void
    {
        $this->user->givePermissionTo('CREAR PROGRAMA DE CARACTERIZACION');
        $this->actingAs($this->user);

        // Usar datos de seeders o crear con factories
        $programa = ProgramaFormacion::factory()->create();
        $jornada = JornadaFormacion::first() ?? JornadaFormacion::factory()->create();
        $sede = Sede::first() ?? Sede::factory()->create();
        $ambiente = Ambiente::first() ?? Ambiente::factory()->create();

        $response = $this->post(route('fichaCaracterizacion.store'), [
            'ficha' => '123456',
            'programa_formacion_id' => $programa->id,
            'jornada_id' => $jornada->id,
            'sede_id' => $sede->id,
            'ambiente_id' => $ambiente->id,
            'fecha_inicio' => now()->format('Y-m-d'),
            'fecha_fin' => now()->addMonths(6)->format('Y-m-d'),
            'total_horas' => 880,
            'status' => true,
            'dias_formacion' => [12, 13, 14], // Días válidos según validación
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fichas_caracterizacion', [
            'ficha' => '123456',
        ]);
    }

    #[Test]
    public function no_puede_crear_ficha_sin_permiso(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('fichaCaracterizacion.store'), []);

        $response->assertStatus(403);
    }

    #[Test]
    public function puede_ver_detalles_de_ficha(): void
    {
        $this->actingAs($this->user);

        $ficha = FichaCaracterizacion::factory()->create();

        $response = $this->get(route('fichaCaracterizacion.show', $ficha->id));

        $response->assertStatus(200);
        $response->assertViewHas('fichaCaracterizacion');
    }

    #[Test]
    public function puede_ver_formulario_de_edicion(): void
    {
        $this->user->givePermissionTo('EDITAR FICHA CARACTERIZACION');
        $this->actingAs($this->user);

        $ficha = FichaCaracterizacion::factory()->create();

        $response = $this->get(route('fichaCaracterizacion.edit', $ficha->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_actualizar_ficha(): void
    {
        $this->user->givePermissionTo('EDITAR FICHA CARACTERIZACION');
        $this->actingAs($this->user);

        $ficha = FichaCaracterizacion::factory()->create();
        $nuevoPrograma = ProgramaFormacion::factory()->create();

        $response = $this->put(route('fichaCaracterizacion.update', $ficha->id), [
            'ficha' => $ficha->ficha,
            'programa_formacion_id' => $nuevoPrograma->id,
            'jornada_id' => $ficha->jornada_id,
            'sede_id' => $ficha->sede_id,
            'ambiente_id' => $ficha->ambiente_id,
            'fecha_inicio' => $ficha->fecha_inicio->format('Y-m-d'),
            'fecha_fin' => $ficha->fecha_fin->format('Y-m-d'),
            'total_horas' => $ficha->total_horas,
            'status' => $ficha->status,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fichas_caracterizacion', [
            'id' => $ficha->id,
            'programa_formacion_id' => $nuevoPrograma->id,
        ]);
    }

    #[Test]
    public function puede_eliminar_ficha(): void
    {
        $this->user->givePermissionTo('ELIMINAR FICHA CARACTERIZACION');
        $this->actingAs($this->user);

        $ficha = FichaCaracterizacion::factory()->create();

        $response = $this->delete(route('fichaCaracterizacion.destroy', $ficha->id));

        $response->assertRedirect();
        $this->assertSoftDeleted('fichas_caracterizacion', [
            'id' => $ficha->id,
        ]);
    }

    #[Test]
    public function requiere_autenticacion_para_ver_fichas(): void
    {
        $response = $this->get(route('fichaCaracterizacion.index'));

        $response->assertRedirect(route('login'));
    }
}
