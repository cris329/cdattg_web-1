<?php

namespace Tests\Unit;

use App\Models\AspiranteComplementario;
use App\Models\ComplementarioOfertado;
use App\Services\AspiranteComplementarioService;
use App\Services\AspiranteDocumentoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AspiranteComplementarioServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AspiranteComplementarioService $service;

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

        $documentoService = app(AspiranteDocumentoService::class);
        $this->service = new AspiranteComplementarioService($documentoService);
    }

    #[Test]
    public function puede_instanciar_servicio(): void
    {
        $this->assertInstanceOf(AspiranteComplementarioService::class, $this->service);
    }

    #[Test]
    public function obtiene_aspirantes_con_documentos(): void
    {
        $complementario = ComplementarioOfertado::factory()->create();
        $aspirantes = $this->service->getAspirantesConDocumentos($complementario->id);

        $this->assertIsIterable($aspirantes);
    }
}


