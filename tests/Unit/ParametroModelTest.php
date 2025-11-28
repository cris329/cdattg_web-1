<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Parametro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ParametroModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);
    }

    #[Test]
    public function puede_crear_parametro(): void
    {
        $parametro = Parametro::factory()->create([
            'name' => 'PARAMETRO TEST',
        ]);

        $this->assertInstanceOf(Parametro::class, $parametro);
        $this->assertEquals('PARAMETRO TEST', $parametro->name);
    }
}

