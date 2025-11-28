<?php

namespace Tests\Unit\Repositories;

use App\Models\Login;
use App\Models\User;
use App\Repositories\LoginRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private LoginRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->repository = new LoginRepository;
    }

    #[Test]
    public function registra_intento_de_login(): void
    {
        $user = User::factory()->create();
        $datos = [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => '127.0.0.1',
            'exitoso' => true,
        ];

        $login = $this->repository->registrar($datos);

        $this->assertDatabaseHas('logins', [
            'user_id' => $user->id,
            'email' => $user->email,
            'exitoso' => true,
        ]);
        $this->assertEquals($user->id, $login->user_id);
    }

    #[Test]
    public function registra_intento_con_datos_por_defecto(): void
    {
        $datos = [
            'email' => 'test@example.com',
            'exitoso' => false,
        ];

        $login = $this->repository->registrar($datos);

        $this->assertNotNull($login->ip_address);
        $this->assertNotNull($login->user_agent);
        $this->assertFalse($login->exitoso);
    }

    #[Test]
    public function obtiene_intentos_recientes_por_usuario(): void
    {
        $user = User::factory()->create();

        Login::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'exitoso' => true,
            'fecha_hora' => now(),
        ]);

        Login::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'exitoso' => false,
            'fecha_hora' => now()->subMinutes(5),
        ]);

        $resultado = $this->repository->obtenerIntentosPorUsuario($user->id, 10);

        $this->assertGreaterThanOrEqual(2, $resultado->count());
    }

    #[Test]
    public function respeta_limite_en_intentos_por_usuario(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 15; $i++) {
            Login::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'exitoso' => true,
                'fecha_hora' => now()->subMinutes($i),
            ]);
        }

        $resultado = $this->repository->obtenerIntentosPorUsuario($user->id, 10);

        $this->assertLessThanOrEqual(10, $resultado->count());
    }

    #[Test]
    public function cuenta_intentos_fallidos_recientes(): void
    {
        $email = 'test@example.com';

        Login::create([
            'email' => $email,
            'exitoso' => false,
            'fecha_hora' => now()->subMinutes(5),
        ]);

        Login::create([
            'email' => $email,
            'exitoso' => false,
            'fecha_hora' => now()->subMinutes(10),
        ]);

        Login::create([
            'email' => $email,
            'exitoso' => false,
            'fecha_hora' => now()->subMinutes(20),
        ]);

        $count = $this->repository->contarIntentosFallidosRecientes($email, 15);

        $this->assertEquals(2, $count);
    }

    #[Test]
    public function no_cuenta_intentos_exitosos(): void
    {
        $email = 'test@example.com';

        Login::create([
            'email' => $email,
            'exitoso' => true,
            'fecha_hora' => now()->subMinutes(5),
        ]);

        Login::create([
            'email' => $email,
            'exitoso' => false,
            'fecha_hora' => now()->subMinutes(10),
        ]);

        $count = $this->repository->contarIntentosFallidosRecientes($email, 15);

        $this->assertEquals(1, $count);
    }

    #[Test]
    public function obtiene_estadisticas(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $fechaInicio = Carbon::today()->format('Y-m-d H:i:s');
        $fechaFin = Carbon::today()->addDay()->format('Y-m-d H:i:s');

        Login::create([
            'user_id' => $user1->id,
            'email' => $user1->email,
            'exitoso' => true,
            'fecha_hora' => now(),
        ]);

        Login::create([
            'user_id' => $user1->id,
            'email' => $user1->email,
            'exitoso' => false,
            'fecha_hora' => now(),
        ]);

        Login::create([
            'user_id' => $user2->id,
            'email' => $user2->email,
            'exitoso' => true,
            'fecha_hora' => now(),
        ]);

        $estadisticas = $this->repository->obtenerEstadisticas($fechaInicio, $fechaFin);

        $this->assertArrayHasKey('total_intentos', $estadisticas);
        $this->assertArrayHasKey('exitosos', $estadisticas);
        $this->assertArrayHasKey('fallidos', $estadisticas);
        $this->assertArrayHasKey('usuarios_unicos', $estadisticas);
        $this->assertGreaterThanOrEqual(2, $estadisticas['total_intentos']);
    }
}
