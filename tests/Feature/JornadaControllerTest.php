<?php

namespace Tests\Feature;

use App\Models\JornadaFormacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class JornadaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        Permission::firstOrCreate(['name' => 'VER JORNADA']);
        Permission::firstOrCreate(['name' => 'CREAR JORNADA']);
        Permission::firstOrCreate(['name' => 'EDITAR JORNADA']);
        Permission::firstOrCreate(['name' => 'ELIMINAR JORNADA']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER JORNADA');
    }

    #[Test]
    public function puede_ver_listado_de_jornadas(): void
    {
        $this->actingAs($this->user);

        JornadaFormacion::factory()->count(5)->create();

        $response = $this->get(route('jornadas.index'));

        $response->assertStatus(200);
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->get(route('jornadas.index'));

        $response->assertRedirect(route('login'));
    }
}
