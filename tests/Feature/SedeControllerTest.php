<?php

namespace Tests\Feature;

use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SedeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);

        Permission::firstOrCreate(['name' => 'VER SEDE']);
        Permission::firstOrCreate(['name' => 'CREAR SEDE']);
        Permission::firstOrCreate(['name' => 'EDITAR SEDE']);
        Permission::firstOrCreate(['name' => 'ELIMINAR SEDE']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER SEDE');
    }

    #[Test]
    public function puede_ver_listado_de_sedes(): void
    {
        $this->actingAs($this->user);

        Sede::factory()->count(5)->create();

        $response = $this->get(route('sede.index'));

        $response->assertStatus(200);
        $response->assertViewIs('sede.index');
    }

    #[Test]
    public function puede_ver_formulario_de_creacion(): void
    {
        $this->user->givePermissionTo('CREAR SEDE');
        $this->actingAs($this->user);

        $response = $this->get(route('sede.create'));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_ver_detalles_de_sede(): void
    {
        $this->actingAs($this->user);

        $sede = Sede::factory()->create();

        $response = $this->get(route('sede.show', $sede->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->get(route('sede.index'));

        $response->assertRedirect(route('login'));
    }
}
