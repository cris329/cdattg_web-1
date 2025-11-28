<?php

namespace Tests\Feature;

use App\Models\CentroFormacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CentroFormacionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);

        Permission::firstOrCreate(['name' => 'VER CENTRO DE FORMACION']);
        Permission::firstOrCreate(['name' => 'CREAR CENTRO DE FORMACION']);
        Permission::firstOrCreate(['name' => 'EDITAR CENTRO DE FORMACION']);
        Permission::firstOrCreate(['name' => 'ELIMINAR CENTRO DE FORMACION']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER CENTRO DE FORMACION');
    }

    #[Test]
    public function puede_ver_listado_de_centros(): void
    {
        $this->actingAs($this->user);

        CentroFormacion::factory()->count(5)->create();

        $response = $this->get(route('centros.index'));

        $response->assertStatus(200);
        $response->assertViewIs('centros.index');
    }

    #[Test]
    public function puede_ver_formulario_de_creacion(): void
    {
        $this->user->givePermissionTo('CREAR CENTRO DE FORMACION');
        $this->actingAs($this->user);

        $response = $this->get(route('centros.create'));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_ver_detalles_de_centro(): void
    {
        $this->actingAs($this->user);

        $centro = CentroFormacion::factory()->create();

        $response = $this->get(route('centros.show', $centro->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->get(route('centros.index'));

        $response->assertRedirect(route('login'));
    }
}
