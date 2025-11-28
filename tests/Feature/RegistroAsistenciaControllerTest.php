<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistroAsistenciaControllerTest extends TestCase
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
    public function puede_registrar_entrada(): void
    {
        $this->actingAs($this->user);

        $instructorFicha = \App\Models\InstructorFichaCaracterizacion::factory()->create();
        $aprendizFicha = \App\Models\AprendizFicha::factory()->create();

        $response = $this->postJson(route('asistencia.registrar.entrada'), [
            'instructor_ficha_id' => $instructorFicha->id,
            'aprendiz_ficha_id' => $aprendizFicha->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'message', 'asistencia']);
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->postJson(route('asistencia.registrar.entrada'), []);

        $response->assertStatus(401);
    }
}
