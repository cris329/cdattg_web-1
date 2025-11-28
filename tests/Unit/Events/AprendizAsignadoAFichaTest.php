<?php

namespace Tests\Unit\Events;

use App\Events\AprendizAsignadoAFicha;
use App\Models\Aprendiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AprendizAsignadoAFichaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function puede_crear_evento(): void
    {
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        $aprendiz = Aprendiz::factory()->create();
        $fichaId = $aprendiz->ficha_caracterizacion_id;

        $event = new AprendizAsignadoAFicha($aprendiz, $fichaId);

        $this->assertInstanceOf(AprendizAsignadoAFicha::class, $event);
        $this->assertEquals($aprendiz->id, $event->aprendiz->id);
        $this->assertEquals($fichaId, $event->fichaId);
    }
}
