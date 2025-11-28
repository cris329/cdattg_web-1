<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ActividadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ActividadServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ActividadService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $this->service = app(ActividadService::class);
    }

    #[Test]
    public function servicio_existe(): void
    {
        $this->assertInstanceOf(ActividadService::class, $this->service);
    }
}

