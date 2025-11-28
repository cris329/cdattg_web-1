<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\PermisoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PermisoServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PermisoService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->service = app(PermisoService::class);
    }

    #[Test]
    public function puede_asignar_permisos(): void
    {
        $user = User::factory()->create();
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'TEST_PERMISSION']);

        $resultado = $this->service->asignarPermisos($user->id, ['TEST_PERMISSION']);

        $this->assertTrue($resultado);
        $this->assertTrue($user->fresh()->hasPermissionTo('TEST_PERMISSION'));
    }

    #[Test]
    public function puede_obtener_permisos_por_rol(): void
    {
        $resultado = $this->service->obtenerPermisosPorRol('INSTRUCTOR');

        $this->assertIsArray($resultado);
        $this->assertContains('TOMAR ASISTENCIA', $resultado);
    }
}
