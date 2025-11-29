<?php

namespace Tests\Complementarios\Unit\Services;

use App\Models\Persona;
use App\Services\AspiranteDocumentoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AspiranteDocumentoServiceTest extends TestCase
{
    use RefreshDatabase;

    private AspiranteDocumentoService $service;

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

        $this->service = new AspiranteDocumentoService;
    }

    #[Test]
    public function construye_patron_busqueda(): void
    {
        $persona = Persona::factory()->create([
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
        ]);

        $patron = $this->service->construirPatronBusqueda($persona);

        $this->assertIsString($patron);
        $this->assertStringContainsString($persona->numero_documento, $patron);
    }

    #[Test]
    public function busca_documento_en_google_drive(): void
    {
        $files = ['documento1.pdf', 'documento2.pdf'];
        $patron = 'CC_1234567890_Juan_Perez_';

        $resultado = $this->service->buscarDocumentoEnGoogleDrive($files, $patron);

        $this->assertIsBool($resultado);
    }

    #[Test]
    public function crea_directorio_temporal(): void
    {
        $directorio = $this->service->createTempDirectory();

        $this->assertIsString($directorio);
        $this->assertStringContainsString('temp', $directorio);
    }
}

