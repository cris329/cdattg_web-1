<?php

namespace Tests\Unit\Observers;

use App\Models\ProgramaFormacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProgramaFormacionObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        Cache::flush();
    }

    #[Test]
    public function invalida_cache_al_crear_programa(): void
    {
        Cache::tags(['programas'])->put('test_key', 'test_value', 60);

        ProgramaFormacion::factory()->create();

        // El observer debe invalidar el cache
        $this->assertNull(Cache::tags(['programas'])->get('test_key'));
    }
}
