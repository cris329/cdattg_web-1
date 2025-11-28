<?php

namespace Tests\Unit;

use App\Models\Parametro;
use App\Models\ParametroTema;
use App\Models\Tema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ParametroTemaModelTest extends TestCase
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
    public function tiene_relacion_con_parametro(): void
    {
        $parametro = Parametro::first();
        $tema = Tema::first();
        $relacion = ParametroTema::factory()->create([
            'parametro_id' => $parametro->id,
            'tema_id' => $tema->id,
        ]);

        $this->assertInstanceOf(Parametro::class, $relacion->parametro);
        $this->assertEquals($parametro->id, $relacion->parametro->id);
    }

    #[Test]
    public function tiene_relacion_con_tema(): void
    {
        $parametro = Parametro::first();
        $tema = Tema::first();
        $relacion = ParametroTema::factory()->create([
            'parametro_id' => $parametro->id,
            'tema_id' => $tema->id,
        ]);

        $this->assertInstanceOf(Tema::class, $relacion->tema);
        $this->assertEquals($tema->id, $relacion->tema->id);
    }
}

