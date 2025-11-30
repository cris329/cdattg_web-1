<?php

namespace Tests\Complementarios\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\Complementarios\AspiranteComplementarioRepository;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AspiranteComplementarioRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected AspiranteComplementarioRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar seeders necesarios para las pruebas
        // Estos datos son requeridos por las claves foráneas en PersonaFactory y ComplementarioOfertado
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\PersonaSeeder::class,
            \Database\Seeders\UsersSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
            \Database\Seeders\CentroFormacionSeeder::class,
            \Database\Seeders\SedeSeeder::class,
            \Database\Seeders\BloqueSeeder::class,
            \Database\Seeders\PisoSeeder::class,
            \Database\Seeders\AmbienteSeeder::class,
            \Database\Seeders\JornadaFormacionSeeder::class,
        ]);
        
        $this->repository = new AspiranteComplementarioRepository();
    }

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function puede_eliminar_aspirante_cambiando_estado()
    {
        $aspirante = AspiranteComplementario::factory()->create(['estado' => 1]);

        $eliminado = $this->repository->delete($aspirante);

        $this->assertTrue($eliminado);
        $this->assertDatabaseHas('aspirantes_complementarios', [
            'id' => $aspirante->id,
            'estado' => 2, // Rechazado
        ]);
    }

    /** @test */
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

    /** @test */
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

    /** @test */
    public function puede_obtener_distribucion_por_programas()
    {
        $programa1 = ComplementarioOfertado::factory()->create();
        $programa2 = ComplementarioOfertado::factory()->create();

        AspiranteComplementario::factory()->count(5)->paraPrograma($programa1)->create();
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa2)->create();

        $distribucion = $this->repository->getDistribucionPorProgramas();

        $this->assertGreaterThanOrEqual(2, $distribucion->count());
    }
}
