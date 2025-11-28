<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PermisoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->user = User::factory()->create();
    }

    #[Test]
    public function puede_ver_listado_de_permisos(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('permiso.index'));

        $response->assertStatus(200);
        $response->assertViewIs('permisos.index');
    }

    #[Test]
    public function puede_ver_detalles_de_permisos_usuario(): void
    {
        $this->actingAs($this->user);

        $targetUser = User::factory()->create();

        $response = $this->get(route('permiso.show', $targetUser->id));

        $response->assertStatus(200);
        $response->assertViewHas(['user', 'permisos', 'roles']);
    }

    #[Test]
    public function puede_asignar_permisos(): void
    {
        $this->actingAs($this->user);

        $targetUser = User::factory()->create();
        $permission = \Spatie\Permission\Models\Permission::first();

        $response = $this->post(route('permiso.store'), [
            'user_id' => $targetUser->id,
            'permisos' => [$permission->id],
        ]);

        $response->assertRedirect(route('permiso.index'));
        $response->assertSessionHas('success');
    }
}
