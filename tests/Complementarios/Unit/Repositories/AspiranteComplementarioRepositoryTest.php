<?php

namespace Tests\Complementarios\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\Complementarios\AspiranteComplementarioRepository;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Complementarios\Concerns\SeedsComplementariosDatabase;

class AspiranteComplementarioRepositoryTest extends TestCase
{
    use RefreshDatabase;
    use SeedsComplementariosDatabase;

    protected AspiranteComplementarioRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seedComplementariosDatabaseIfNeeded();
        
        $this->repository = new AspiranteComplementarioRepository();
    }

    #[Test]
    public function puede_encontrar_aspirantes_por_programa()
    {
        $programa = ComplementarioOfertado::factory()->create();
        AspiranteComplementario::factory()->count(5)->paraPrograma($programa)->create();
        AspiranteComplementario::factory()->count(3)->create(); // Otro programa

        $aspirantes = $this->repository->findByPrograma($programa->id);

        $this->assertCount(5, $aspirantes);
        $aspirantes->each(function ($aspirante) use ($programa) {
            $this->assertEquals($programa->id, $aspirante->complementario_id);
        });
    }

    #[Test]
    public function puede_encontrar_aspirantes_con_documentos()
    {
        $programa = ComplementarioOfertado::factory()->create();
        $personaConDoc = Persona::factory()->create(['condocumento' => 1]);
        $personaSinDoc = Persona::factory()->create(['condocumento' => 0]);

        AspiranteComplementario::factory()->paraPrograma($programa)->paraPersona($personaConDoc)->create();
        AspiranteComplementario::factory()->paraPrograma($programa)->paraPersona($personaSinDoc)->create();

        $aspirantes = $this->repository->findByProgramaConDocumentos($programa->id);

        $this->assertCount(1, $aspirantes);
        $this->assertEquals($personaConDoc->id, $aspirantes->first()->persona_id);
    }

    #[Test]
    public function puede_contar_aspirantes_por_estado()
    {
        $programa = ComplementarioOfertado::factory()->create();
        AspiranteComplementario::factory()->count(3)->enProceso()->paraPrograma($programa)->create();
        AspiranteComplementario::factory()->count(2)->admitido()->paraPrograma($programa)->create();

        $enProceso = $this->repository->countByEstado($programa->id, 1);
        $admitidos = $this->repository->countByEstado($programa->id, 3);

        $this->assertEquals(3, $enProceso);
        $this->assertEquals(2, $admitidos);
    }

    #[Test]
    public function puede_verificar_si_existe_inscripcion()
    {
        $persona = Persona::factory()->create();
        $programa = ComplementarioOfertado::factory()->create();
        AspiranteComplementario::factory()->paraPersona($persona)->paraPrograma($programa)->create();

        $existe = $this->repository->existeInscripcion($persona->id, $programa->id);
        $noExiste = $this->repository->existeInscripcion($persona->id, ComplementarioOfertado::factory()->create()->id);

        $this->assertTrue($existe);
        $this->assertFalse($noExiste);
    }

    #[Test]
    public function puede_crear_nuevo_aspirante()
    {
        $persona = Persona::factory()->create();
        $programa = ComplementarioOfertado::factory()->create();

        $aspirante = $this->repository->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
            'estado' => 1,
            'observaciones' => 'Test observaciones',
        ]);

        $this->assertDatabaseHas('aspirantes_complementarios', [
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
            'estado' => 1,
        ]);
        $this->assertEquals($persona->id, $aspirante->persona_id);
    }

    #[Test]
    public function puede_actualizar_aspirante()
    {
        $aspirante = AspiranteComplementario::factory()->enProceso()->create();

        $actualizado = $this->repository->update($aspirante, ['estado' => 3]);

        $this->assertTrue($actualizado);
        $this->assertDatabaseHas('aspirantes_complementarios', [
            'id' => $aspirante->id,
            'estado' => 3,
        ]);
    }

    #[Test]
    public function puede_eliminar_aspirante_cambiando_estado()
    {
        $aspirante = AspiranteComplementario::factory()->create(['estado' => 1]);

        $eliminado = $this->repository->delete($aspirante);

        $this->assertTrue($eliminado);
        $this->assertDatabaseHas('aspirantes_complementarios', [
            'id' => $aspirante->id,
            'estado' => 4, // Rechazado
        ]);
    }

    #[Test]
    public function puede_obtener_estadisticas()
    {
        AspiranteComplementario::factory()->count(5)->enProceso()->create();
        AspiranteComplementario::factory()->count(3)->admitido()->create();
        AspiranteComplementario::factory()->count(2)->rechazado()->create();

        $estadisticas = $this->repository->getEstadisticas();

        $this->assertEquals(10, $estadisticas['total']);
        $this->assertEquals(5, $estadisticas['activos']);
        $this->assertEquals(3, $estadisticas['aceptados']);
        $this->assertEquals(2, $estadisticas['rechazados']);
    }

    #[Test]
    public function puede_obtener_tendencia_inscripciones()
    {
        // Crear aspirantes en diferentes meses
        AspiranteComplementario::factory()->count(3)->create([
            'created_at' => now()->subMonths(2),
        ]);
        AspiranteComplementario::factory()->count(2)->create([
            'created_at' => now()->subMonth(),
        ]);

        $tendencia = $this->repository->getTendenciaInscripciones(6);

        $this->assertGreaterThan(0, $tendencia->count());
    }

    #[Test]
    public function puede_obtener_distribucion_por_programas()
    {
        $programa1 = ComplementarioOfertado::factory()->create();
        $programa2 = ComplementarioOfertado::factory()->create();

        AspiranteComplementario::factory()->count(5)->paraPrograma($programa1)->create();
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa2)->create();

        $distribucion = $this->repository->getDistribucionPorProgramas();

        $this->assertGreaterThanOrEqual(2, $distribucion->count());
    }

    #[Test]
    public function puede_encontrar_aspirantes_con_documentos_excluyendo_rechazados()
    {
        $programa = ComplementarioOfertado::factory()->create();
        $personaConDoc = Persona::factory()->create(['condocumento' => 1]);
        $personaSinDoc = Persona::factory()->create(['condocumento' => 0]);
        $personaConDocRechazado = Persona::factory()->create(['condocumento' => 1]);

        AspiranteComplementario::factory()->paraPrograma($programa)->paraPersona($personaConDoc)->enProceso()->create();
        AspiranteComplementario::factory()->paraPrograma($programa)->paraPersona($personaSinDoc)->enProceso()->create();
        AspiranteComplementario::factory()->paraPrograma($programa)->paraPersona($personaConDocRechazado)->rechazado()->create();

        $aspirantes = $this->repository->findByProgramaConDocumentosExcluyendoRechazados($programa->id);

        $this->assertCount(1, $aspirantes);
        $this->assertEquals($personaConDoc->id, $aspirantes->first()->persona_id);
    }

    #[Test]
    public function puede_encontrar_aspirantes_para_exportacion()
    {
        $programa = ComplementarioOfertado::factory()->create();
        $personaValida = Persona::factory()->create([
            'condocumento' => 1,
            'estado_sofia' => 1,
        ]);
        $personaSinDoc = Persona::factory()->create([
            'condocumento' => 0,
            'estado_sofia' => 1,
        ]);
        $personaNoRegistrada = Persona::factory()->create([
            'condocumento' => 1,
            'estado_sofia' => 0,
        ]);
        $personaRechazada = Persona::factory()->create([
            'condocumento' => 1,
            'estado_sofia' => 1,
        ]);

        AspiranteComplementario::factory()->paraPrograma($programa)->paraPersona($personaValida)->enProceso()->create();
        AspiranteComplementario::factory()->paraPrograma($programa)->paraPersona($personaSinDoc)->enProceso()->create();
        AspiranteComplementario::factory()->paraPrograma($programa)->paraPersona($personaNoRegistrada)->enProceso()->create();
        AspiranteComplementario::factory()->paraPrograma($programa)->paraPersona($personaRechazada)->rechazado()->create();

        $aspirantes = $this->repository->findByProgramaParaExportacion($programa->id);

        $this->assertCount(1, $aspirantes);
        $this->assertEquals($personaValida->id, $aspirantes->first()->persona_id);
    }

    #[Test]
    public function puede_obtener_estadisticas_exclusion()
    {
        $programa = ComplementarioOfertado::factory()->create();
        
        // Crear personas con diferentes estados
        $personaValida = Persona::factory()->create([
            'condocumento' => 1,
            'estado_sofia' => 1,
        ]);
        $personaSinDoc = Persona::factory()->create([
            'condocumento' => 0,
            'estado_sofia' => 1,
        ]);
        $personaNoRegistrada = Persona::factory()->create([
            'condocumento' => 1,
            'estado_sofia' => 0,
        ]);
        $personaRechazada = Persona::factory()->create([
            'condocumento' => 1,
            'estado_sofia' => 1,
        ]);

        // Crear aspirantes
        AspiranteComplementario::factory()->paraPrograma($programa)->paraPersona($personaValida)->enProceso()->create();
        AspiranteComplementario::factory()->paraPrograma($programa)->paraPersona($personaSinDoc)->enProceso()->create();
        AspiranteComplementario::factory()->paraPrograma($programa)->paraPersona($personaNoRegistrada)->enProceso()->create();
        AspiranteComplementario::factory()->paraPrograma($programa)->paraPersona($personaRechazada)->rechazado()->create();

        $estadisticas = $this->repository->getEstadisticasExclusion($programa->id);

        $this->assertEquals(4, $estadisticas['total']);
        $this->assertEquals(1, $estadisticas['rechazados']);
        $this->assertEquals(1, $estadisticas['sin_documento']);
        $this->assertEquals(1, $estadisticas['no_registrados_sofia']);
        $this->assertEquals(1, $estadisticas['validos']);
    }

    #[Test]
    public function puede_contar_aspirantes_por_programa()
    {
        $programa1 = ComplementarioOfertado::factory()->create();
        $programa2 = ComplementarioOfertado::factory()->create();

        AspiranteComplementario::factory()->count(5)->paraPrograma($programa1)->create();
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa2)->create();

        $count1 = $this->repository->countByPrograma($programa1->id);
        $count2 = $this->repository->countByPrograma($programa2->id);

        $this->assertEquals(5, $count1);
        $this->assertEquals(3, $count2);
    }

    #[Test]
    public function puede_encontrar_aspirante_por_persona_y_programa()
    {
        $persona = Persona::factory()->create();
        $programa = ComplementarioOfertado::factory()->create();
        $aspirante = AspiranteComplementario::factory()->paraPersona($persona)->paraPrograma($programa)->create();

        $encontrado = $this->repository->findByPersonaYPrograma($persona->id, $programa->id);
        $noEncontrado = $this->repository->findByPersonaYPrograma($persona->id, ComplementarioOfertado::factory()->create()->id);

        $this->assertNotNull($encontrado);
        $this->assertEquals($aspirante->id, $encontrado->id);
        $this->assertNull($noEncontrado);
    }

    #[Test]
    public function puede_encontrar_aspirante_por_id()
    {
        $aspirante = AspiranteComplementario::factory()->create();

        $encontrado = $this->repository->findById($aspirante->id);
        $noEncontrado = $this->repository->findById(99999);

        $this->assertNotNull($encontrado);
        $this->assertEquals($aspirante->id, $encontrado->id);
        $this->assertNull($noEncontrado);
    }

    #[Test]
    public function puede_obtener_aspirantes_para_exportacion_con_caracterizacion()
    {
        $programa = ComplementarioOfertado::factory()->create();
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa)->create();

        $aspirantes = $this->repository->findForExport($programa->id);

        $this->assertCount(3, $aspirantes);
        $aspirantes->each(function ($aspirante) {
            $this->assertTrue($aspirante->relationLoaded('persona'));
        });
    }

    #[Test]
    public function puede_obtener_tendencia_inscripciones_con_diferentes_meses()
    {
        // Crear aspirantes en diferentes meses
        AspiranteComplementario::factory()->count(3)->create([
            'created_at' => now()->subMonths(2),
        ]);
        AspiranteComplementario::factory()->count(2)->create([
            'created_at' => now()->subMonth(),
        ]);
        AspiranteComplementario::factory()->count(1)->create([
            'created_at' => now(),
        ]);

        $tendencia = $this->repository->getTendenciaInscripciones(6);

        $this->assertGreaterThan(0, $tendencia->count());
        $tendencia->each(function ($item) {
            $this->assertIsObject($item);
            $this->assertTrue(isset($item->year) || property_exists($item, 'year'));
            $this->assertTrue(isset($item->month) || property_exists($item, 'month'));
            $this->assertTrue(isset($item->total) || property_exists($item, 'total'));
        });
    }

    #[Test]
    public function puede_obtener_distribucion_por_programas_con_nombres()
    {
        $programa1 = ComplementarioOfertado::factory()->create(['nombre' => 'Programa A']);
        $programa2 = ComplementarioOfertado::factory()->create(['nombre' => 'Programa B']);

        AspiranteComplementario::factory()->count(5)->paraPrograma($programa1)->create();
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa2)->create();

        $distribucion = $this->repository->getDistribucionPorProgramas();

        $this->assertGreaterThanOrEqual(2, $distribucion->count());
        $distribucion->each(function ($item) {
            $this->assertIsObject($item);
            $this->assertTrue(isset($item->programa) || property_exists($item, 'programa'));
            $this->assertTrue(isset($item->total) || property_exists($item, 'total'));
        });
    }

    #[Test]
    public function count_by_estado_retorna_cero_si_no_hay_aspirantes()
    {
        $programa = ComplementarioOfertado::factory()->create();

        $count = $this->repository->countByEstado($programa->id, 1);

        $this->assertEquals(0, $count);
    }

    #[Test]
    public function count_by_programa_retorna_cero_si_no_hay_aspirantes()
    {
        $programa = ComplementarioOfertado::factory()->create();

        $count = $this->repository->countByPrograma($programa->id);

        $this->assertEquals(0, $count);
    }

    #[Test]
    public function puede_encontrar_aspirantes_por_programa_con_relaciones_personalizadas()
    {
        $programa = ComplementarioOfertado::factory()->create();
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa)->create();

        $aspirantes = $this->repository->findByPrograma($programa->id, ['persona']);

        $this->assertCount(3, $aspirantes);
        $aspirantes->each(function ($aspirante) {
            $this->assertTrue($aspirante->relationLoaded('persona'));
        });
    }
}
