<?php

namespace Tests\Feature;

use App\Models\Piso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PisoControllerTest extends TestCase
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
        ]);

        Permission::firstOrCreate(['name' => 'VER PISO']);
        Permission::firstOrCreate(['name' => 'CREAR PISO']);
        Permission::firstOrCreate(['name' => 'EDITAR PISO']);
        Permission::firstOrCreate(['name' => 'ELIMINAR PISO']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER PISO');
    }

    #[Test]
    public function puede_ver_listado_de_pisos(): void
    {
        $this->actingAs($this->user);

        Piso::factory()->count(5)->create();

        $response = $this->get(route('pisos.index'));

        $response->assertStatus(200);
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->get(route('pisos.index'));

        $response->assertRedirect(route('login'));
    }
}
