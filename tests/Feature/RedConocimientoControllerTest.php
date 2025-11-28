<?php

namespace Tests\Feature;

use App\Models\RedConocimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RedConocimientoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);

        Permission::firstOrCreate(['name' => 'VER RED CONOCIMIENTO']);
        Permission::firstOrCreate(['name' => 'CREAR RED CONOCIMIENTO']);
        Permission::firstOrCreate(['name' => 'EDITAR RED CONOCIMIENTO']);
        Permission::firstOrCreate(['name' => 'ELIMINAR RED CONOCIMIENTO']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('VER RED CONOCIMIENTO');
    }

    #[Test]
    public function puede_ver_listado_de_redes(): void
    {
        $this->actingAs($this->user);

        RedConocimiento::factory()->count(5)->create();

        $response = $this->get(route('red-conocimiento.index'));

        $response->assertStatus(200);
        $response->assertViewIs('red_conocimiento.index');
    }

    #[Test]
    public function puede_ver_formulario_de_creacion(): void
    {
        $this->user->givePermissionTo('CREAR RED CONOCIMIENTO');
        $this->actingAs($this->user);

        $response = $this->get(route('red-conocimiento.create'));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_ver_detalles_de_red(): void
    {
        $this->actingAs($this->user);

        $red = RedConocimiento::factory()->create();

        $response = $this->get(route('red-conocimiento.show', $red->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function requiere_autenticacion(): void
    {
        $response = $this->get(route('red-conocimiento.index'));

        $response->assertRedirect(route('login'));
    }
}
