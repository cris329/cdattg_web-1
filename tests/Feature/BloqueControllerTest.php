<?php

namespace Tests\Feature;

use App\Models\Bloque;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BloqueControllerTest extends TestCase
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

        Permission::firstOrCreate(['name' => 'VER BLOQUE']);
        Permission::firstOrCreate(['name' => 'CREAR BLOQUE']);
        Permission::firstOrCreate(['name' => 'EDITAR BLOQUE']);
        Permission::firstOrCreate(['name' => 'ELIMINAR BLOQUE']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER BLOQUE');
    }

    #[Test]
    public function puede_ver_listado_de_bloques(): void
    {
        $this->actingAs($this->user);

        Bloque::factory()->count(5)->create();

        $response = $this->get(route('bloques.index'));

        $response->assertStatus(200);
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->get(route('bloques.index'));

        $response->assertRedirect(route('login'));
    }
}
