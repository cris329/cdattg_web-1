<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\JornadaFormacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class JornadaFormacionModelTest extends TestCase
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
    public function puede_crear_jornada(): void
    {
        $jornada = JornadaFormacion::factory()->create([
            'jornada' => 'DIURNA',
        ]);

        $this->assertInstanceOf(JornadaFormacion::class, $jornada);
        $this->assertEquals('DIURNA', $jornada->jornada);
    }
}

