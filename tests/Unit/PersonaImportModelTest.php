<?php

namespace Tests\Unit;

use App\Models\PersonaContactAlert;
use App\Models\PersonaImport;
use App\Models\PersonaImportIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonaImportModelTest extends TestCase
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
    public function tiene_relacion_con_user(): void
    {
        $user = User::factory()->create();
        $import = PersonaImport::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $import->user);
        $this->assertEquals($user->id, $import->user->id);
    }

    #[Test]
    public function tiene_relacion_con_issues(): void
    {
        $import = PersonaImport::factory()->create();
        PersonaImportIssue::factory()->count(2)->create([
            'persona_import_id' => $import->id,
        ]);

        $this->assertCount(2, $import->issues);
    }

    #[Test]
    public function tiene_relacion_con_contact_alerts(): void
    {
        $import = PersonaImport::factory()->create();
        PersonaContactAlert::factory()->count(2)->create([
            'persona_import_id' => $import->id,
        ]);

        $this->assertCount(2, $import->contactAlerts);
    }
}

