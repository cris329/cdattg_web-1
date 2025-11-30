<?php

namespace Tests\Inventario\Unit\Repositories;

use Tests\TestCase;
use App\Inventario\Repositories\User\UserRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;
    private const ROL_SUPER_ADMINISTRADOR = 'SUPER ADMINISTRADOR';

    protected UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository();
        
        // Ejecutar seeders necesarios
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\PersonaSeeder::class,
            \Database\Seeders\UsersSeeder::class,
        ]);
    }

    #[Test]
    public function puede_obtener_super_administradores()
    {
        // Crear rol si no existe
        Role::firstOrCreate(['name' => self::ROL_SUPER_ADMINISTRADOR]);
        
        // Crear usuario con rol
        $user = User::first();
        if (!$user->hasRole(self::ROL_SUPER_ADMINISTRADOR)) {
            $user->assignRole(self::ROL_SUPER_ADMINISTRADOR);
        }

        $resultado = $this->repository->obtenerSuperAdministradores();

        $this->assertGreaterThanOrEqual(1, $resultado->count());
        $this->assertTrue($resultado->first()->hasRole(self::ROL_SUPER_ADMINISTRADOR));
    }

    #[Test]
    public function retorna_coleccion_vacia_si_no_hay_super_administradores()
    {
        // Asegurarse de que no hay usuarios con el rol
        $users = User::role(self::ROL_SUPER_ADMINISTRADOR)->get();
        foreach ($users as $user) {
            $user->removeRole(self::ROL_SUPER_ADMINISTRADOR);
        }

        $resultado = $this->repository->obtenerSuperAdministradores();

        $this->assertCount(0, $resultado);
    }
}

