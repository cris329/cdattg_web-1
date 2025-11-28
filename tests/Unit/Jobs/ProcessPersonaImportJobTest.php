<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessPersonaImportJob;
use App\Models\PersonaImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcessPersonaImportJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);
    }

    #[Test]
    public function puede_crear_job(): void
    {
        $import = PersonaImport::factory()->create();
        $job = new ProcessPersonaImportJob($import->id);

        $this->assertInstanceOf(ProcessPersonaImportJob::class, $job);
        $this->assertEquals($import->id, $job->importId);
    }

    #[Test]
    public function tiene_configuracion_de_reintentos(): void
    {
        $import = PersonaImport::factory()->create();
        $job = new ProcessPersonaImportJob($import->id);

        $this->assertEquals(3, $job->tries);
        $this->assertGreaterThan(0, $job->timeout);
    }
}
