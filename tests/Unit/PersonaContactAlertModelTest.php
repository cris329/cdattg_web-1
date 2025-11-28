<?php

namespace Tests\Unit;

use App\Models\Persona;
use App\Models\PersonaContactAlert;
use App\Models\PersonaImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonaContactAlertModelTest extends TestCase
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
    public function tiene_relacion_con_persona(): void
    {
        $persona = Persona::factory()->create();
        $alert = PersonaContactAlert::factory()->create([
            'persona_id' => $persona->id,
        ]);

        $this->assertInstanceOf(Persona::class, $alert->persona);
        $this->assertEquals($persona->id, $alert->persona->id);
    }

    #[Test]
    public function tiene_relacion_con_import(): void
    {
        $import = PersonaImport::factory()->create();
        $alert = PersonaContactAlert::factory()->create([
            'persona_import_id' => $import->id,
        ]);

        $this->assertInstanceOf(PersonaImport::class, $alert->import);
        $this->assertEquals($import->id, $alert->import->id);
    }
}

