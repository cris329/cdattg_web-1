<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EstadisticasControllerTest extends TestCase
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
    public function puede_ver_dashboard(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('estadisticas.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('estadisticas.dashboard');
    }

    #[Test]
    public function puede_obtener_api_estadisticas(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('estadisticas.api'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->get(route('estadisticas.dashboard'));

        $response->assertRedirect(route('login'));
    }
}
