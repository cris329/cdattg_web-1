<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConfiguracionControllerTest extends TestCase
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
    public function puede_obtener_fichas_activas(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('configuracion.fichas-activas'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    #[Test]
    public function puede_obtener_regionales_activas(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('configuracion.regionales-activas'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->getJson(route('configuracion.fichas-activas'));

        $response->assertStatus(401);
    }
}
