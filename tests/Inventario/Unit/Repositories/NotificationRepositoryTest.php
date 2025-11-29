<?php

namespace Tests\Inventario\Unit\Repositories;

use Tests\TestCase;
use App\Inventario\Repositories\Notification\NotificationRepository;
use App\Models\User;
use App\Models\Inventario\Notificacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class NotificationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new NotificationRepository();
        
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
    public function puede_obtener_notificaciones_paginadas_por_usuario()
    {
        $user = User::first();
        
        // Crear notificaciones para el usuario
        Notificacion::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'tipo' => 'TestNotification',
            'notificable_type' => User::class,
            'notificable_id' => $user->id,
            'datos' => json_encode(['message' => 'Test']),
            'leida_en' => null,
        ]);

        $resultado = $this->repository->obtenerPorUsuarioPaginadas($user->id, 10);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }

    #[Test]
    public function puede_obtener_notificaciones_no_leidas_limitadas()
    {
        $user = User::first();
        
        Notificacion::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'tipo' => 'TestNotification',
            'notificable_type' => User::class,
            'notificable_id' => $user->id,
            'datos' => json_encode(['message' => 'Test']),
            'leida_en' => null,
        ]);

        $resultado = $this->repository->obtenerNoLeidasLimitadas($user->id, 5);

        $this->assertGreaterThanOrEqual(1, $resultado->count());
    }

    #[Test]
    public function puede_contar_notificaciones_no_leidas()
    {
        $user = User::first();
        
        Notificacion::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'tipo' => 'TestNotification',
            'notificable_type' => User::class,
            'notificable_id' => $user->id,
            'datos' => json_encode(['message' => 'Test']),
            'leida_en' => null,
        ]);

        $resultado = $this->repository->contarNoLeidas($user->id);

        $this->assertGreaterThanOrEqual(1, $resultado);
    }

    #[Test]
    public function puede_marcar_notificacion_como_leida()
    {
        $user = User::first();
        $notification = Notificacion::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'tipo' => 'TestNotification',
            'notificable_type' => User::class,
            'notificable_id' => $user->id,
            'datos' => json_encode(['message' => 'Test']),
            'leida_en' => null,
        ]);

        $resultado = $this->repository->marcarComoLeida($user->id, $notification->id);

        $this->assertTrue($resultado);
        $this->assertNotNull(Notificacion::find($notification->id)->leida_en);
    }

    #[Test]
    public function puede_marcar_todas_las_notificaciones_como_leidas()
    {
        $user = User::first();
        
        Notificacion::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'tipo' => 'TestNotification',
            'notificable_type' => User::class,
            'notificable_id' => $user->id,
            'datos' => json_encode(['message' => 'Test 1']),
            'leida_en' => null,
        ]);
        
        Notificacion::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'tipo' => 'TestNotification',
            'notificable_type' => User::class,
            'notificable_id' => $user->id,
            'datos' => json_encode(['message' => 'Test 2']),
            'leida_en' => null,
        ]);

        $resultado = $this->repository->marcarTodasComoLeidas($user->id);

        $this->assertGreaterThanOrEqual(2, $resultado);
    }

    #[Test]
    public function puede_eliminar_notificacion()
    {
        $user = User::first();
        $notification = Notificacion::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'tipo' => 'TestNotification',
            'notificable_type' => User::class,
            'notificable_id' => $user->id,
            'datos' => json_encode(['message' => 'Test']),
            'leida_en' => null,
        ]);

        $resultado = $this->repository->eliminar($user->id, $notification->id);

        $this->assertTrue($resultado);
        $this->assertNull(Notificacion::find($notification->id));
    }

    #[Test]
    public function retorna_false_si_notificacion_no_existe_al_marcar_como_leida()
    {
        $user = User::first();
        $notificationId = \Illuminate\Support\Str::uuid()->toString();

        $resultado = $this->repository->marcarComoLeida($user->id, $notificationId);

        $this->assertFalse($resultado);
    }

    #[Test]
    public function retorna_false_si_notificacion_no_existe_al_eliminar()
    {
        $user = User::first();
        $notificationId = \Illuminate\Support\Str::uuid()->toString();

        $resultado = $this->repository->eliminar($user->id, $notificationId);

        $this->assertFalse($resultado);
    }
}

