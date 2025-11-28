<?php

namespace Tests\Unit;

use App\Repositories\TemaRepository;
use App\Services\PersonaImportNormalizer;
use App\Services\PersonaImportService;
use App\Services\PersonaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonaImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PersonaImportService $service;

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

        $personaService = app(PersonaService::class);
        $temaRepository = app(TemaRepository::class);

        $this->service = new PersonaImportService($personaService, $temaRepository);
    }

    #[Test]
    public function puede_instanciar_servicio(): void
    {
        $this->assertInstanceOf(PersonaImportService::class, $this->service);
    }
}


