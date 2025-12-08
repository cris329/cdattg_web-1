<?php

namespace Tests\Complementarios\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Persona;
use App\Models\Departamento;
use App\Models\Municipio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Complementarios\Concerns\SeedsComplementariosDatabase;

class EstadisticaComplementarioControllerTest extends TestCase
{
    use RefreshDatabase;
    use SeedsComplementariosDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seedComplementariosDatabaseIfNeeded();
        
        $this->user = User::factory()->create();
    }

    #[Test]
    public function puede_ver_estadisticas_dashboard()
    {
        $this->actingAs($this->user);
        
        $response = $this->get(route('complementarios.estadisticas'));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.estadisticas');
        $response->assertViewHas('departamentos');
        $response->assertViewHas('municipios');
        // Las estadísticas ahora las obtiene el componente Livewire, no el controlador
        // Verificamos que la vista se renderiza correctamente
        $response->assertSee('Estadísticas', false);
    }

    #[Test]
    public function puede_obtener_estadisticas_api_sin_filtros()
    {
        $this->actingAs($this->user);
        
        // Crear datos de prueba
        $programa = ComplementarioOfertado::factory()->create(['estado' => 1]);
        AspiranteComplementario::factory()->count(5)->paraPrograma($programa)->create();
        AspiranteComplementario::factory()->admitido()->count(2)->paraPrograma($programa)->create();
        AspiranteComplementario::factory()->enProceso()->count(1)->paraPrograma($programa)->create();

        $response = $this->get(route('complementarios.estadisticas.api'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_aspirantes',
            'aspirantes_aceptados',
            'aspirantes_pendientes',
            'programas_activos',
            'tendencia_inscripciones',
            'distribucion_programas',
            'programas_demanda',
        ]);
    }

    #[Test]
    public function puede_obtener_estadisticas_api_con_filtro_fecha()
    {
        $this->actingAs($this->user);
        
        $fechaInicio = now()->subDays(30)->format('Y-m-d');
        $fechaFin = now()->format('Y-m-d');

        $response = $this->get(route('complementarios.estadisticas.api', [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_filtrado',
            'aceptados_filtrado',
            'pendientes_filtrado',
            'datos',
        ]);
    }

    #[Test]
    public function puede_obtener_estadisticas_api_con_filtro_departamento()
    {
        $this->actingAs($this->user);
        
        $departamento = Departamento::first();
        
        if ($departamento) {
            $response = $this->get(route('complementarios.estadisticas.api', [
                'departamento_id' => $departamento->id,
            ]));

            $response->assertStatus(200);
            $response->assertJsonStructure([
                'total_filtrado',
                'aceptados_filtrado',
                'pendientes_filtrado',
                'datos',
            ]);
        } else {
            $this->markTestSkipped('No hay departamentos en la base de datos');
        }
    }

    #[Test]
    public function puede_obtener_estadisticas_api_con_filtro_municipio()
    {
        $this->actingAs($this->user);
        
        $municipio = Municipio::first();
        
        if ($municipio) {
            $response = $this->get(route('complementarios.estadisticas.api', [
                'municipio_id' => $municipio->id,
            ]));

            $response->assertStatus(200);
            $response->assertJsonStructure([
                'total_filtrado',
                'aceptados_filtrado',
                'pendientes_filtrado',
                'datos',
            ]);
        } else {
            $this->markTestSkipped('No hay municipios en la base de datos');
        }
    }

    #[Test]
    public function puede_obtener_estadisticas_api_con_filtro_programa()
    {
        $this->actingAs($this->user);
        
        $programa = ComplementarioOfertado::factory()->create();
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa)->create();

        $response = $this->get(route('complementarios.estadisticas.api', [
            'programa_id' => $programa->id,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_filtrado',
            'aceptados_filtrado',
            'pendientes_filtrado',
            'datos',
        ]);
    }

    #[Test]
    public function puede_obtener_estadisticas_api_con_filtros_combinados()
    {
        $this->actingAs($this->user);
        
        $programa = ComplementarioOfertado::factory()->create();
        $departamento = Departamento::first();
        
        if ($departamento) {
            $fechaInicio = now()->subDays(30)->format('Y-m-d');
            $fechaFin = now()->format('Y-m-d');

            $response = $this->get(route('complementarios.estadisticas.api', [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'departamento_id' => $departamento->id,
                'programa_id' => $programa->id,
            ]));

            $response->assertStatus(200);
            $response->assertJsonStructure([
                'total_filtrado',
                'aceptados_filtrado',
                'pendientes_filtrado',
                'datos',
            ]);
        } else {
            $this->markTestSkipped('No hay departamentos en la base de datos');
        }
    }

    #[Test]
    public function puede_exportar_programas_demanda_excel()
    {
        $this->actingAs($this->user);
        
        // Crear programas con aspirantes para tener datos de demanda
        $programa1 = ComplementarioOfertado::factory()->create(['estado' => 1]);
        $programa2 = ComplementarioOfertado::factory()->create(['estado' => 1]);
        
        AspiranteComplementario::factory()->count(5)->paraPrograma($programa1)->create();
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa2)->create();

        $response = $this->get(route('complementarios.estadisticas.exportar-excel'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        
        // Verificar que el header Content-Disposition contiene 'attachment' y '.xlsx'
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertNotNull($contentDisposition);
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString('.xlsx', $contentDisposition);
    }

    #[Test]
    public function exportar_excel_maneja_errores_correctamente()
    {
        $this->actingAs($this->user);
        
        // Mock del servicio para forzar un error
        $mockService = $this->mock(\App\Services\Complementarios\EstadisticaComplementarioService::class);
        $mockService->shouldReceive('exportarProgramasDemandaExcel')
            ->once()
            ->andThrow(new \Exception('Error de prueba'));

        $this->app->instance(
            \App\Services\Complementarios\EstadisticaComplementarioService::class,
            $mockService
        );

        $response = $this->get(route('complementarios.estadisticas.exportar-excel'));

        $response->assertStatus(500);
    }

    #[Test]
    public function estadisticas_dashboard_muestra_departamentos_y_municipios()
    {
        $this->actingAs($this->user);
        
        $response = $this->get(route('complementarios.estadisticas'));

        $response->assertStatus(200);
        $departamentos = $response->viewData('departamentos');
        $municipios = $response->viewData('municipios');
        
        $this->assertNotNull($departamentos);
        $this->assertNotNull($municipios);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $departamentos);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $municipios);
    }

    #[Test]
    public function estadisticas_api_retorna_datos_vacios_cuando_no_hay_datos()
    {
        $this->actingAs($this->user);
        
        // No crear ningún dato

        $response = $this->get(route('complementarios.estadisticas.api'));

        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertArrayHasKey('total_aspirantes', $data);
        $this->assertArrayHasKey('aspirantes_aceptados', $data);
        $this->assertArrayHasKey('aspirantes_pendientes', $data);
        $this->assertArrayHasKey('programas_activos', $data);
    }
}

