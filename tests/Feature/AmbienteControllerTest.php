<?php

namespace Tests\Feature;

use App\Models\Ambiente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AmbienteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
            \Database\Seeders\BloqueSeeder::class,
            \Database\Seeders\PisoSeeder::class,
        ]);

        Permission::firstOrCreate(['name' => 'VER AMBIENTE']);
        Permission::firstOrCreate(['name' => 'CREAR AMBIENTE']);
        Permission::firstOrCreate(['name' => 'EDITAR AMBIENTE']);
        Permission::firstOrCreate(['name' => 'ELIMINAR AMBIENTE']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER AMBIENTE');
    }

    #[Test]
    public function puede_ver_listado_de_ambientes(): void
    {
        $this->actingAs($this->user);

        Ambiente::factory()->count(5)->create();

        $response = $this->get(route('ambiente.index'));

        $response->assertStatus(200);
        $response->assertViewIs('ambiente.index');
    }

    #[Test]
    public function puede_ver_formulario_de_creacion(): void
    {
        $this->user->givePermissionTo('CREAR AMBIENTE');
        $this->actingAs($this->user);

        $response = $this->get(route('ambiente.create'));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_ver_detalles_de_ambiente(): void
    {
        $this->actingAs($this->user);

        $ambiente = Ambiente::factory()->create();

        $response = $this->get(route('ambiente.show', $ambiente->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->get(route('ambiente.index'));

        $response->assertRedirect(route('login'));
    }
}
