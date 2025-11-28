<?php

namespace Tests\Feature;

use App\Models\Competencia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CompetenciaControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        Permission::firstOrCreate(['name' => 'VER COMPETENCIA']);
        Permission::firstOrCreate(['name' => 'CREAR COMPETENCIA']);
        Permission::firstOrCreate(['name' => 'EDITAR COMPETENCIA']);
        Permission::firstOrCreate(['name' => 'ELIMINAR COMPETENCIA']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER COMPETENCIA');
    }

    #[Test]
    public function puede_ver_listado_de_competencias(): void
    {
        $this->actingAs($this->user);

        Competencia::factory()->count(5)->create();

        $response = $this->get(route('competencias.index'));

        $response->assertStatus(200);
        $response->assertViewIs('competencias.index');
        $response->assertViewHas('competencias');
    }

    #[Test]
    public function puede_buscar_competencias(): void
    {
        $this->actingAs($this->user);

        Competencia::factory()->create(['nombre' => 'Competencia Test']);

        $response = $this->get(route('competencias.index', ['search' => 'Test']));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_crear_competencia(): void
    {
        $this->user->givePermissionTo('CREAR COMPETENCIA');
        $this->actingAs($this->user);

        $response = $this->post(route('competencias.store'), [
            'codigo' => 'COMP-'.$this->faker->unique()->numerify('####'),
            'nombre' => 'Competencia de Prueba',
            'descripcion' => $this->faker->sentence(),
            'duracion' => $this->faker->randomFloat(2, 10, 200),
            'fecha_inicio' => now()->format('Y-m-d'),
            'fecha_fin' => now()->addMonths(6)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    #[Test]
    public function puede_ver_detalles_de_competencia(): void
    {
        $this->actingAs($this->user);

        $competencia = Competencia::factory()->create();

        $response = $this->get(route('competencias.show', $competencia->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_eliminar_competencia(): void
    {
        $this->user->givePermissionTo('ELIMINAR COMPETENCIA');
        $this->actingAs($this->user);

        $competencia = Competencia::factory()->create();

        $response = $this->delete(route('competencias.destroy', $competencia->id));

        $response->assertRedirect();
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->get(route('competencias.index'));

        $response->assertRedirect(route('login'));
    }
}
