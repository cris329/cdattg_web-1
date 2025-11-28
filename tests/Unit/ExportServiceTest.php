<?php

namespace Tests\Unit;

use App\Services\ExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExportServiceTest extends TestCase
{
    use RefreshDatabase;

    private ExportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ExportService;
    }

    #[Test]
    public function exporta_datos_a_excel(): void
    {
        $datos = collect([
            ['nombre' => 'Juan', 'edad' => 25],
            ['nombre' => 'María', 'edad' => 30],
        ]);

        $columnas = [
            ['field' => 'nombre', 'label' => 'Nombre'],
            ['field' => 'edad', 'label' => 'Edad'],
        ];

        $path = $this->service->exportarExcel($datos, $columnas, 'Test');

        $this->assertStringContainsString('exports/', $path);
        $this->assertStringContainsString('.xlsx', $path);
    }

    #[Test]
    public function exporta_datos_a_csv(): void
    {
        $datos = collect([
            ['nombre' => 'Juan', 'edad' => 25],
            ['nombre' => 'María', 'edad' => 30],
        ]);

        $columnas = [
            ['field' => 'nombre', 'label' => 'Nombre'],
            ['field' => 'edad', 'label' => 'Edad'],
        ];

        $path = $this->service->exportarCSV($datos, $columnas, 'Test');

        $this->assertStringContainsString('exports/', $path);
        $this->assertStringContainsString('.csv', $path);
    }

    #[Test]
    public function exporta_datos_a_json(): void
    {
        $datos = collect([
            ['nombre' => 'Juan', 'edad' => 25],
            ['nombre' => 'María', 'edad' => 30],
        ]);

        $path = $this->service->exportarJSON($datos, 'Test');

        $this->assertStringContainsString('exports/', $path);
        $this->assertStringContainsString('.json', $path);
    }
}

