<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Tema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class TemaModelTest extends TestCase
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
    public function puede_crear_tema(): void
    {
        $tema = Tema::factory()->create([
            'name' => 'TEMA TEST',
        ]);

        $this->assertInstanceOf(Tema::class, $tema);
        $this->assertEquals('TEMA TEST', $tema->name);
    }
}

