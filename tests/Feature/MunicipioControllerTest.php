<?php

namespace Tests\Feature;

use App\Models\Municipio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MunicipioControllerTest extends TestCase
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

        $this->user = User::factory()->create();
    }

    #[Test]
    public function puede_ver_listado_de_municipios(): void
    {
        $this->actingAs($this->user);

        Municipio::factory()->count(5)->create();

        $response = $this->get(route('municipios.index'));

        $response->assertStatus(200);
    }

    #[Test]
    public function puede_cargar_municipios_por_departamento(): void
    {
        $this->actingAs($this->user);

        $departamento = \App\Models\Departamento::first();
        Municipio::factory()->count(3)->create(['departamento_id' => $departamento->id]);

        $response = $this->getJson(route('municipios.cargar', $departamento->id));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'municipios']);
    }
}
