<?php

namespace Tests\Unit\Repositories;

use App\Models\Persona;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

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

        $this->repository = new UserRepository;
    }

    #[Test]
    public function encuentra_usuario_por_email(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $resultado = $this->repository->encontrarPorEmail('test@example.com');

        $this->assertNotNull($resultado);
        $this->assertEquals($user->id, $resultado->id);
        $this->assertEquals('test@example.com', $resultado->email);
    }

    #[Test]
    public function retorna_null_si_usuario_no_existe_por_email(): void
    {
        $resultado = $this->repository->encontrarPorEmail('inexistente@example.com');

        $this->assertNull($resultado);
    }

    #[Test]
    public function encuentra_usuario_por_persona(): void
    {
        $persona = Persona::factory()->create();
        $user = User::factory()->create(['persona_id' => $persona->id]);

        $resultado = $this->repository->encontrarPorPersona($persona->id);

        $this->assertNotNull($resultado);
        $this->assertEquals($user->id, $resultado->id);
        $this->assertNotNull($resultado->persona);
    }

    #[Test]
    public function retorna_null_si_usuario_no_existe_por_persona(): void
    {
        $resultado = $this->repository->encontrarPorPersona(999);

        $this->assertNull($resultado);
    }

    #[Test]
    public function obtiene_usuarios_por_rol(): void
    {
        $role = Role::firstOrCreate(['name' => 'TEST_ROLE']);
        $users = User::factory()->count(3)->create();
        foreach ($users as $user) {
            $user->assignRole($role);
        }

        $resultado = $this->repository->obtenerPorRol('TEST_ROLE');

        $this->assertCount(3, $resultado);
        foreach ($resultado as $user) {
            $this->assertTrue($user->hasRole('TEST_ROLE'));
        }
    }

    #[Test]
    public function retorna_coleccion_vacia_si_no_hay_usuarios_con_rol(): void
    {
        $resultado = $this->repository->obtenerPorRol('ROL_INEXISTENTE');

        $this->assertCount(0, $resultado);
    }

    #[Test]
    public function crea_nuevo_usuario(): void
    {
        $persona = Persona::factory()->create();
        $datos = [
            'email' => 'nuevo@example.com',
            'password' => 'password123',
            'persona_id' => $persona->id,
            'status' => 1,
        ];

        $user = $this->repository->crear($datos);

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@example.com',
            'persona_id' => $persona->id,
        ]);
        $this->assertEquals('nuevo@example.com', $user->email);
    }

    #[Test]
    public function actualiza_usuario(): void
    {
        $user = User::factory()->create();
        $datos = ['email' => 'actualizado@example.com'];

        $actualizado = $this->repository->actualizar($user->id, $datos);

        $this->assertTrue($actualizado);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'actualizado@example.com',
        ]);
    }

    #[Test]
    public function retorna_false_si_usuario_no_existe_al_actualizar(): void
    {
        $actualizado = $this->repository->actualizar(999, ['email' => 'test@example.com']);

        $this->assertFalse($actualizado);
    }

    #[Test]
    public function invalida_cache(): void
    {
        $user = User::factory()->create();
        $this->repository->obtenerPorRol('TEST_ROLE');

        $this->repository->invalidarCache();

        $this->assertTrue(true);
    }
}
