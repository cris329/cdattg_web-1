<?php

namespace Tests\Feature;

use App\Models\EntradaSalida;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EntradaSalidaControllerTest extends TestCase
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
    public function puede_ver_listado_de_entradas_salidas(): void
    {
        $this->actingAs($this->user);

        EntradaSalida::factory()->count(5)->create([
            'instructor_user_id' => $this->user->id,
        ]);

        $response = $this->get(route('entrada-salida.index'));

        $response->assertStatus(200);
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->get(route('entrada-salida.index'));

        $response->assertRedirect(route('login'));
    }
}
