<?php

namespace Tests\Unit\Observers;

use App\Models\FichaCaracterizacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FichaCaracterizacionObserverTest extends TestCase
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
    public function invalida_cache_al_crear_ficha(): void
    {
        Cache::tags(['fichas'])->put('test_key', 'test_value', 60);

        FichaCaracterizacion::factory()->create();

        // El observer debe invalidar el cache
        $this->assertNull(Cache::tags(['fichas'])->get('test_key'));
    }

    #[Test]
    public function invalida_cache_al_actualizar_ficha(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        Cache::tags(['fichas'])->put('test_key', 'test_value', 60);

        $ficha->update(['status' => false]);

        // El observer debe invalidar el cache
        $this->assertNull(Cache::tags(['fichas'])->get('test_key'));
    }
}
