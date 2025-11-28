<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
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
    public function puede_ver_perfil(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('profile.index'));

        $response->assertStatus(200);
        $response->assertViewIs('profile.index');
        $response->assertViewHas(['user', 'persona']);
    }

    #[Test]
    public function puede_actualizar_perfil(): void
    {
        $this->actingAs($this->user);

        $response = $this->put(route('profile.update'), [
            'name' => 'Nuevo Nombre',
            'email' => $this->user->email,
        ]);

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function puede_cambiar_contrasena(): void
    {
        $this->actingAs($this->user);

        $currentPassword = 'password';
        $this->user->update(['password' => Hash::make($currentPassword)]);

        $response = $this->post(route('profile.change-password'), [
            'current_password' => $currentPassword,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->get(route('profile.index'));

        $response->assertRedirect(route('login'));
    }
}
