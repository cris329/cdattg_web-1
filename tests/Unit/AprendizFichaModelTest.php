<?php

namespace Tests\Unit;

use App\Models\Aprendiz;
use App\Models\AprendizFicha;
use App\Models\FichaCaracterizacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AprendizFichaModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);
    }

    #[Test]
    public function tiene_relacion_con_aprendiz(): void
    {
        $aprendiz = Aprendiz::factory()->create();
        $aprendizFicha = AprendizFicha::factory()->create(['aprendiz_id' => $aprendiz->id]);

        $this->assertInstanceOf(Aprendiz::class, $aprendizFicha->aprendiz);
        $this->assertEquals($aprendiz->id, $aprendizFicha->aprendiz->id);
    }

    #[Test]
    public function tiene_relacion_con_ficha(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $aprendizFicha = AprendizFicha::factory()->create(['ficha_id' => $ficha->id]);

        $this->assertInstanceOf(FichaCaracterizacion::class, $aprendizFicha->ficha);
        $this->assertEquals($ficha->id, $aprendizFicha->ficha->id);
    }
}

