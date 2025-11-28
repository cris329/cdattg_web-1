<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function puede_ver_formulario_login(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('adminlte::auth.login');
    }

    #[Test]
    public function puede_iniciar_sesion_con_credenciales_validas(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'status' => 1,
        ]);

        $response = $this->post(route('login.iniciar'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function no_puede_iniciar_sesion_con_credenciales_invalidas(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login.iniciar'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        $this->assertGuest();
    }
}
