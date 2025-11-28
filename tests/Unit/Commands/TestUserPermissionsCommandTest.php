<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\TestUserPermissions;
use App\Models\FichaCaracterizacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TestUserPermissionsCommandTest extends TestCase
{
    use RefreshDatabase;

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
    }

    #[Test]
    public function command_existe(): void
    {
        $command = new TestUserPermissions;

        $this->assertEquals(
            'test:user-permissions',
            $command->getName()
        );
    }

    #[Test]
    public function muestra_error_si_usuario_no_existe(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();

        Artisan::call('test:user-permissions', [
            'user_id' => 999,
            'ficha_id' => $ficha->id,
        ]);

        $output = Artisan::output();
        $this->assertStringContainsString('Usuario con ID 999 no encontrado', $output);
    }

    #[Test]
    public function muestra_error_si_ficha_no_existe(): void
    {
        $user = User::factory()->create();

        Artisan::call('test:user-permissions', [
            'user_id' => $user->id,
            'ficha_id' => 999,
        ]);

        $output = Artisan::output();
        $this->assertStringContainsString('Ficha con ID 999 no encontrada', $output);
    }

    #[Test]
    public function muestra_informacion_del_usuario(): void
    {
        $user = User::factory()->create();
        $ficha = FichaCaracterizacion::factory()->create();

        Artisan::call('test:user-permissions', [
            'user_id' => $user->id,
            'ficha_id' => $ficha->id,
        ]);

        $output = Artisan::output();
        $this->assertStringContainsString('INFORMACIÓN DEL USUARIO', $output);
        $this->assertStringContainsString('PERMISOS DEL USUARIO', $output);
        $this->assertStringContainsString('VERIFICACIÓN DE AUTORIZACIÓN', $output);
    }
}
