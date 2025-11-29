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
        $role = Role::firstOrCreate(['name' => 'SUPER ADMINISTRADOR']);
        
        // Crear usuario con rol
        $user = User::first();
        if (!$user->hasRole('SUPER ADMINISTRADOR')) {
            $user->assignRole('SUPER ADMINISTRADOR');
        }

        $resultado = $this->repository->obtenerSuperAdministradores();

        $this->assertGreaterThanOrEqual(1, $resultado->count());
        $this->assertTrue($resultado->first()->hasRole('SUPER ADMINISTRADOR'));
    }

    #[Test]
    public function retorna_coleccion_vacia_si_no_hay_super_administradores()
    {
        // Asegurarse de que no hay usuarios con el rol
        $users = User::role('SUPER ADMINISTRADOR')->get();
        foreach ($users as $user) {
            $user->removeRole('SUPER ADMINISTRADOR');
        }

        $resultado = $this->repository->obtenerSuperAdministradores();

        $this->assertCount(0, $resultado);
    }
}

