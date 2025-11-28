<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogoutControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function puede_cerrar_sesion(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect('/');
        $response->assertSessionHas('success');
        $this->assertGuest();
    }

    #[Test]
    public function puede_cerrar_sesion_api(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson(route('logout.api'));

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Sesión cerrada correctamente']);
        $this->assertGuest();
    }
}
