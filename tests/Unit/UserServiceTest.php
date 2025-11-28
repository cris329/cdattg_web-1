<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->service = app(UserService::class);
    }

    #[Test]
    public function puede_cambiar_estado_usuario(): void
    {
        $user1 = User::factory()->create(['status' => true]);
        $user2 = User::factory()->create(['status' => false]);

        $this->actingAs($user1);

        $resultado = $this->service->cambiarEstado($user2->id, $user1->id);

        $this->assertTrue($resultado);
        $this->assertTrue($user2->fresh()->status);
    }

    #[Test]
    public function no_puede_cambiar_estado_propio(): void
    {
        $user = User::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No puedes modificar tu propio estado.');

        $this->service->cambiarEstado($user->id, $user->id);
    }

    #[Test]
    public function puede_asignar_roles(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'TEST_ROLE']);

        $resultado = $this->service->asignarRoles($user->id, ['TEST_ROLE']);

        $this->assertTrue($resultado);
        $this->assertTrue($user->fresh()->hasRole('TEST_ROLE'));
    }
}
