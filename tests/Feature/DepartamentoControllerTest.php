<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DepartamentoControllerTest extends TestCase
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
    public function puede_cargar_departamentos_activos(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('departamentos.cargar'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'departamentos']);
    }
}
