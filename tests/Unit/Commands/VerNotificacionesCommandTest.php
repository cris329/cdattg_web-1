<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\VerNotificaciones;
use App\Models\Inventario\Notificacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VerNotificacionesCommandTest extends TestCase
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
        $command = new VerNotificaciones;

        $this->assertEquals(
            'ver:notificaciones',
            $command->getName()
        );
    }

    #[Test]
    public function ejecuta_comando_sin_errores(): void
    {
        Artisan::call('ver:notificaciones');

        $this->assertEquals(0, Artisan::exitCode());
    }

    #[Test]
    public function muestra_total_de_notificaciones(): void
    {
        Artisan::call('ver:notificaciones');

        $output = Artisan::output();
        $this->assertStringContainsString('NOTIFICACIONES EN LA BASE DE DATOS', $output);
        $this->assertStringContainsString('Total:', $output);
    }

    #[Test]
    public function muestra_lista_de_notificaciones(): void
    {
        $user = User::factory()->create();

        Notificacion::factory()->create([
            'notificable_type' => User::class,
            'notificable_id' => $user->id,
        ]);

        Artisan::call('ver:notificaciones');

        $output = Artisan::output();
        $this->assertStringContainsString('ID:', $output);
        $this->assertEquals(0, Artisan::exitCode());
    }
}
