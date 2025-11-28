<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserControllerTest extends TestCase
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
    public function puede_cambiar_estado_usuario(): void
    {
        $this->actingAs($this->user);

        $targetUser = User::factory()->create(['status' => 1]);

        $response = $this->post(route('users.toggle-status', $targetUser->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('users.toggle-status', $user->id));

        $response->assertRedirect(route('login'));
    }
}
