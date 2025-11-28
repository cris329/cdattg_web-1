<?php

namespace Tests\Feature;

use App\Models\Tema;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TemaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        Permission::firstOrCreate(['name' => 'VER TEMA']);
        Permission::firstOrCreate(['name' => 'CREAR TEMA']);
        Permission::firstOrCreate(['name' => 'EDITAR TEMA']);
        Permission::firstOrCreate(['name' => 'ELIMINAR TEMA']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER TEMA');
    }

    #[Test]
    public function puede_ver_listado_de_temas(): void
    {
        $this->actingAs($this->user);

        Tema::factory()->count(5)->create();

        $response = $this->get(route('temas.index'));

        $response->assertStatus(200);
        $response->assertViewIs('temas.index');
    }

    #[Test]
    public function puede_ver_detalles_de_tema(): void
    {
        $this->actingAs($this->user);

        $tema = Tema::factory()->create();

        $response = $this->get(route('temas.show', $tema->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->get(route('temas.index'));

        $response->assertRedirect(route('login'));
    }
}
