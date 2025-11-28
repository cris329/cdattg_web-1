<?php

namespace Tests\Feature;

use App\Models\GuiasAprendizaje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class GuiaAprendizajeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        Permission::firstOrCreate(['name' => 'VER GUIA APRENDIZAJE']);
        Permission::firstOrCreate(['name' => 'CREAR GUIA APRENDIZAJE']);
        Permission::firstOrCreate(['name' => 'EDITAR GUIA APRENDIZAJE']);
        Permission::firstOrCreate(['name' => 'ELIMINAR GUIA APRENDIZAJE']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER GUIA APRENDIZAJE');
    }

    #[Test]
    public function puede_ver_listado_de_guias(): void
    {
        $this->actingAs($this->user);

        GuiasAprendizaje::factory()->count(5)->create();

        $response = $this->get(route('guias-aprendizaje.index'));

        $response->assertStatus(200);
        $response->assertViewIs('guias_aprendizaje.index');
    }

    #[Test]
    public function puede_ver_detalles_de_guia(): void
    {
        $this->actingAs($this->user);

        $guia = GuiasAprendizaje::factory()->create();

        $response = $this->get(route('guias-aprendizaje.show', $guia->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->get(route('guias-aprendizaje.index'));

        $response->assertRedirect(route('login'));
    }
}
