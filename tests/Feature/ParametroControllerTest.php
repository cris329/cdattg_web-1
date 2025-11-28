<?php

namespace Tests\Feature;

use App\Models\Parametro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ParametroControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        Permission::firstOrCreate(['name' => 'VER PARAMETRO']);
        Permission::firstOrCreate(['name' => 'CREAR PARAMETRO']);
        Permission::firstOrCreate(['name' => 'EDITAR PARAMETRO']);
        Permission::firstOrCreate(['name' => 'ELIMINAR PARAMETRO']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER PARAMETRO');
    }

    #[Test]
    public function puede_ver_listado_de_parametros(): void
    {
        $this->actingAs($this->user);

        Parametro::factory()->count(5)->create();

        $response = $this->get(route('parametros.index'));

        $response->assertStatus(200);
        $response->assertViewIs('parametros.index');
    }

    #[Test]
    public function puede_ver_detalles_de_parametro(): void
    {
        $this->actingAs($this->user);

        $parametro = Parametro::factory()->create();

        $response = $this->get(route('parametros.show', $parametro->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->get(route('parametros.index'));

        $response->assertRedirect(route('login'));
    }
}
