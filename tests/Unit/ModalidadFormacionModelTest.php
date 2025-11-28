<?php

namespace Tests\Unit;

use App\Models\FichaCaracterizacion;
use App\Models\ModalidadFormacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModalidadFormacionModelTest extends TestCase
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
    public function tiene_relacion_con_fichas_caracterizacion(): void
    {
        $modalidad = ModalidadFormacion::factory()->create();
        FichaCaracterizacion::factory()->count(2)->create([
            'modalidad_formacion_id' => $modalidad->id,
        ]);

        $this->assertCount(2, $modalidad->fichasCaracterizacion);
    }

    #[Test]
    public function puede_crear_modalidad(): void
    {
        $modalidad = ModalidadFormacion::create([
            'modalidad_formacion' => 'Presencial',
        ]);

        $this->assertDatabaseHas('modalidades_formacion', [
            'id' => $modalidad->id,
            'modalidad_formacion' => 'Presencial',
        ]);
    }
}

