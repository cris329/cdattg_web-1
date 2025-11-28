<?php

namespace Tests\Unit;

use App\Models\PersonaImport;
use App\Models\PersonaImportIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonaImportIssueModelTest extends TestCase
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
    public function tiene_relacion_con_import(): void
    {
        $import = PersonaImport::factory()->create();
        $issue = PersonaImportIssue::factory()->create([
            'persona_import_id' => $import->id,
        ]);

        $this->assertInstanceOf(PersonaImport::class, $issue->import);
        $this->assertEquals($import->id, $issue->import->id);
    }

    #[Test]
    public function puede_almacenar_raw_payload_como_array(): void
    {
        $import = PersonaImport::factory()->create();
        $issue = PersonaImportIssue::factory()->create([
            'persona_import_id' => $import->id,
            'raw_payload' => ['campo1' => 'valor1', 'campo2' => 'valor2'],
        ]);

        $this->assertIsArray($issue->raw_payload);
        $this->assertEquals('valor1', $issue->raw_payload['campo1']);
    }
}

