<?php

namespace Tests\Feature;

use App\Models\Regional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RegionalControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
        ]);

        Permission::firstOrCreate(['name' => 'VER REGIONAL']);
        Permission::firstOrCreate(['name' => 'CREAR REGIONAL']);
        Permission::firstOrCreate(['name' => 'EDITAR REGIONAL']);
        Permission::firstOrCreate(['name' => 'ELIMINAR REGIONAL']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER REGIONAL');
    }

    #[Test]
    public function puede_ver_listado_de_regionales(): void
    {
        $this->actingAs($this->user);

        Regional::factory()->count(5)->create();

        $response = $this->get(route('regional.index'));

        $response->assertStatus(200);
        $response->assertViewIs('regional.index');
    }

    #[Test]
    public function puede_ver_detalles_de_regional(): void
    {
        $this->actingAs($this->user);

        $regional = Regional::factory()->create();

        $response = $this->get(route('regional.show', $regional->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_crear_regional(): void
    {
        $this->user->givePermissionTo('CREAR REGIONAL');
        $this->actingAs($this->user);

        $response = $this->post(route('regional.store'), [
            'regional' => 'Regional Test',
            'departamento_id' => \App\Models\Departamento::first()->id,
        ]);

        $response->assertRedirect(route('regional.index'));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->get(route('regional.index'));

        $response->assertRedirect(route('login'));
    }
}
