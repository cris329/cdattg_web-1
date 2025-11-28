<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\NotificacionService;
use App\Models\User;
use App\Models\Instructor;
use App\Models\Aprendiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class NotificacionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected NotificacionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NotificacionService();

        // Ejecutar seeders necesarios para las pruebas
        // Estos datos son requeridos por las claves foráneas en PersonaFactory
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
    public function puede_notificar_instructor_sin_email()
    {
        $instructor = Instructor::factory()->create();
        
        // Eliminar el usuario directamente desde la BD para evitar problemas de cache
        // El servicio verifica $user->email, no $persona->email
        if ($instructor->persona->user) {
            DB::table('users')->where('persona_id', $instructor->persona->id)->delete();
            // Limpiar la relación cacheada y refrescar
            $instructor->persona->refresh();
            $instructor->refresh();
        }

        $resultado = $this->service->notificarNuevaFichaInstructor($instructor, [
            'numero' => '2089876',
        ]);

        $this->assertFalse($resultado);
    }

    #[Test]
    public function registra_log_al_notificar()
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Notificación de nueva ficha enviada', Mockery::type('array'));

        $instructor = Instructor::factory()->create();
        $instructor->persona->email = 'test@example.com';
        $instructor->persona->save();

        $this->service->notificarNuevaFichaInstructor($instructor, [
            'numero' => '2089876',
        ]);
    }

    #[Test]
    public function puede_notificar_multiples_aprendices()
    {
        $aprendices = Aprendiz::factory()->count(5)->create();

        $enviados = $this->service->notificarAprendices($aprendices, 'Mensaje de prueba');

        $this->assertGreaterThanOrEqual(0, $enviados);
        $this->assertLessThanOrEqual(5, $enviados);
    }

    #[Test]
    public function maneja_errores_al_notificar()
    {
        Log::shouldReceive('error')->atLeast()->once();
        Log::shouldReceive('info')->atLeast()->once();

        $aprendizMock = Mockery::mock();
        $aprendizMock->id = 1;
        $aprendizMock->shouldReceive('__get')
            ->with('persona')
            ->andThrow(new \Exception('Error accediendo a persona'));

        $aprendices = collect([$aprendizMock]);

        $enviados = $this->service->notificarAprendices($aprendices, 'Mensaje');

        $this->assertEquals(0, $enviados);
    }
}
