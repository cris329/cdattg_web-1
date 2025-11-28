<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\TestNotificaciones;
use App\Models\Inventario\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TestNotificacionesCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);
    }

    #[Test]
    public function command_existe(): void
    {
        $command = new TestNotificaciones;

        $this->assertEquals(
            'test:notificaciones',
            $command->getName()
        );
    }

    #[Test]
    public function muestra_error_si_no_hay_superadmin(): void
    {
        Artisan::call('test:notificaciones');

        $output = Artisan::output();
        $this->assertStringContainsString('No se encontró ningún usuario con rol SUPER ADMINISTRADOR', $output);
        $this->assertEquals(1, Artisan::exitCode());
    }

    #[Test]
    public function crea_notificaciones_con_superadmin(): void
    {
        $role = Role::firstOrCreate(['name' => 'SUPER ADMINISTRADOR']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $producto = Producto::factory()->create();

        Artisan::call('test:notificaciones');

        $output = Artisan::output();
        $this->assertStringContainsString('Usuario encontrado', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }
}
