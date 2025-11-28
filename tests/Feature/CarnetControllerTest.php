<?php

namespace Tests\Feature;

use App\Models\Aprendiz;
use App\Models\FichaCaracterizacion;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CarnetControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Seeders base para datos realistas
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);

        // Crear usuario autenticado
        $this->user = User::factory()->create();
    }

    #[Test]
    public function puede_generar_carnet_aprendiz(): void
    {
        $this->actingAs($this->user);

        $ficha = FichaCaracterizacion::factory()->create();
        $aprendiz = Aprendiz::factory()->create([
            'ficha_caracterizacion_id' => $ficha->id,
        ]);

        $response = $this->postJson(route('carnet.generar.aprendiz', $aprendiz->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'archivo',
            'url',
        ]);
    }

    #[Test]
    public function puede_generar_carnet_instructor(): void
    {
        $this->actingAs($this->user);

        $instructor = Instructor::factory()->create();

        $response = $this->postJson(route('carnet.generar.instructor', $instructor->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'archivo',
            'url',
        ]);
    }

    #[Test]
    public function puede_generar_carnets_masivos(): void
    {
        $this->actingAs($this->user);

        $ficha = FichaCaracterizacion::factory()->create();
        Aprendiz::factory()->count(3)->create([
            'ficha_caracterizacion_id' => $ficha->id,
        ]);

        $response = $this->postJson(route('carnet.generar.masivos'), [
            'ficha_id' => $ficha->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'total',
        ]);
    }

    #[Test]
    public function no_puede_generar_carnets_masivos_sin_aprendices(): void
    {
        $this->actingAs($this->user);

        $ficha = FichaCaracterizacion::factory()->create();

        $response = $this->postJson(route('carnet.generar.masivos'), [
            'ficha_id' => $ficha->id,
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'No hay aprendices en esta ficha',
        ]);
    }

    #[Test]
    public function puede_verificar_carnet(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('carnet.verificar'), [
            'qr_data' => 'test-qr-data',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
        ]);
    }

    #[Test]
    public function requiere_autenticacion_para_generar_carnet(): void
    {
        $aprendiz = Aprendiz::factory()->create();

        $response = $this->postJson(route('carnet.generar.aprendiz', $aprendiz->id));

        $response->assertStatus(401);
    }
}
