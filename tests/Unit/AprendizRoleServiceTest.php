<?php

namespace Tests\Unit;

use App\Models\Aprendiz;
use App\Models\Persona;
use App\Models\User;
use App\Services\AprendizRoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AprendizRoleServiceTest extends TestCase
{
    use RefreshDatabase;

    private AprendizRoleService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);

        $this->service = new AprendizRoleService;
    }

    #[Test]
    public function asegura_rol_aprendiz(): void
    {
        $persona = Persona::factory()->create(['email' => 'test@example.com']);
        $user = User::factory()->create(['persona_id' => $persona->id]);
        $aprendiz = Aprendiz::factory()->create(['persona_id' => $persona->id]);

        $resultado = $this->service->ensureAprendizRole($aprendiz);

        $this->assertTrue($resultado);
        $this->assertTrue($user->fresh()->hasRole('APRENDIZ'));
    }

    #[Test]
    public function sincroniza_roles_con_usuario(): void
    {
        $persona = Persona::factory()->create();
        $user = User::factory()->create(['persona_id' => $persona->id]);
        $aprendiz = Aprendiz::factory()->create(['persona_id' => $persona->id]);

        $rol = Role::firstOrCreate(['name' => 'APRENDIZ']);
        $user->assignRole($rol);

        $this->service->syncRolesWithUser($aprendiz);

        $this->assertTrue($aprendiz->hasRole('APRENDIZ'));
    }
}

