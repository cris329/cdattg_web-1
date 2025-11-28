<?php

namespace Tests\Unit;

use App\Models\Aprendiz;
use App\Models\Instructor;
use App\Models\Persona;
use App\Models\ProgramaFormacion;
use App\Repositories\AprendizRepository;
use App\Repositories\FichaRepository;
use App\Repositories\InstructorRepository;
use App\Repositories\ProgramaFormacionRepository;
use App\Services\BusquedaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BusquedaServiceTest extends TestCase
{
    use RefreshDatabase;

    private BusquedaService $service;

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

        $this->service = new BusquedaService(
            app(AprendizRepository::class),
            app(InstructorRepository::class),
            app(FichaRepository::class),
            app(ProgramaFormacionRepository::class)
        );
    }

    #[Test]
    public function realiza_busqueda_global(): void
    {
        $persona = Persona::factory()->create(['primer_nombre' => 'Juan']);
        Aprendiz::factory()->create(['persona_id' => $persona->id]);

        $resultado = $this->service->busquedaGlobal('Juan');

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('termino', $resultado);
        $this->assertArrayHasKey('resultados', $resultado);
    }

    #[Test]
    public function realiza_busqueda_por_tipos_especificos(): void
    {
        $resultado = $this->service->busquedaGlobal('test', ['aprendices']);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('resultados', $resultado);
    }

    #[Test]
    public function obtiene_sugerencias_de_busqueda(): void
    {
        $persona = Persona::factory()->create(['primer_nombre' => 'Juan']);
        Aprendiz::factory()->create(['persona_id' => $persona->id]);

        $sugerencias = $this->service->obtenerSugerencias('Juan');

        $this->assertIsArray($sugerencias);
    }

    #[Test]
    public function no_retorna_sugerencias_si_termino_es_muy_corto(): void
    {
        $sugerencias = $this->service->obtenerSugerencias('Ju');

        $this->assertIsArray($sugerencias);
        $this->assertCount(0, $sugerencias);
    }
}

