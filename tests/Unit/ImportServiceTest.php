<?php

namespace Tests\Unit;

use App\Models\FichaCaracterizacion;
use App\Repositories\AprendizRepository;
use App\Services\ImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ImportService $service;

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

        $this->service = app(ImportService::class);
    }

    #[Test]
    public function puede_instanciar_servicio(): void
    {
        $this->assertInstanceOf(ImportService::class, $this->service);
    }

    #[Test]
    public function importar_aprendices_csv_requiere_archivo_valido(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();

        $archivoPath = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($archivoPath, "numero_documento;primer_nombre;primer_apellido;email\n123456;Juan;Pérez;test@test.com");

        $resultado = $this->service->importarAprendicesCSV($archivoPath, $ficha->id);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('exitoso', $resultado);

        unlink($archivoPath);
    }
}


