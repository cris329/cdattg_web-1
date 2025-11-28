<?php

namespace Tests\Unit;

use App\Models\ProgramaFormacion;
use App\Models\RedConocimiento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProgramaFormacionModelTest extends TestCase
{
    use RefreshDatabase;

    protected ProgramaFormacion $programa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        $this->programa = ProgramaFormacion::factory()->create();
    }

    #[Test]
    public function tiene_relacion_con_red_conocimiento(): void
    {
        $this->assertInstanceOf(RedConocimiento::class, $this->programa->redConocimiento);
        $this->assertNotNull($this->programa->redConocimiento);
    }

    #[Test]
    public function tiene_relacion_con_fichas(): void
    {
        \App\Models\FichaCaracterizacion::factory()->create([
            'programa_formacion_id' => $this->programa->id,
        ]);

        $this->assertTrue($this->programa->fichas()->exists());
    }

    #[Test]
    public function puede_verificar_si_esta_activo(): void
    {
        $programa = ProgramaFormacion::factory()->create(['status' => true]);

        $this->assertTrue($programa->status);
    }
}
