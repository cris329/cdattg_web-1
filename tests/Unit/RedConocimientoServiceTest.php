<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\RedConocimientoService;
use App\Models\RedConocimiento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class RedConocimientoServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RedConocimientoService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);

        $this->service = app(RedConocimientoService::class);
    }

    #[Test]
    public function puede_listar_redes(): void
    {
        RedConocimiento::factory()->count(5)->create();

        $resultado = $this->service->listar(10);

        $this->assertGreaterThanOrEqual(5, $resultado->total());
    }
}

