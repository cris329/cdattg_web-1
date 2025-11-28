<?php

namespace Tests\Unit;

use App\Services\AsignacionInstructorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AsignacionInstructorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AsignacionInstructorService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);

        $this->service = app(AsignacionInstructorService::class);
    }

    #[Test]
    public function servicio_existe(): void
    {
        $this->assertInstanceOf(AsignacionInstructorService::class, $this->service);
    }

    #[Test]
    public function puede_instanciar_servicio(): void
    {
        $service = app(AsignacionInstructorService::class);

        $this->assertInstanceOf(AsignacionInstructorService::class, $service);
    }
}
