<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProfileService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->service = app(ProfileService::class);
    }

    #[Test]
    public function puede_actualizar_perfil(): void
    {
        $user = User::factory()->create([
            'name' => 'Juan',
            'email' => 'juan@example.com',
        ]);

        $datos = [
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@example.com',
        ];

        $resultado = $this->service->actualizarPerfil($user, $datos);

        $this->assertTrue($resultado);
        $this->assertEquals('Juan Pérez', $user->fresh()->name);
        $this->assertEquals('juan.perez@example.com', $user->fresh()->email);
    }

    #[Test]
    public function puede_cambiar_contrasena(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $resultado = $this->service->cambiarContrasena($user, 'password123', 'newpassword456');

        $this->assertTrue($resultado);
        $this->assertTrue(Hash::check('newpassword456', $user->fresh()->password));
    }

    #[Test]
    public function no_puede_cambiar_contrasena_con_actual_incorrecta(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('La contraseña actual no es correcta');

        $this->service->cambiarContrasena($user, 'wrongpassword', 'newpassword456');
    }
}
