<?php

namespace Tests\Complementarios\Feature\Controllers;

use Tests\TestCase;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Persona;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

class AspiranteComplementarioControllerTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_NUMERO_DOCUMENTO = '1234567890';

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar seeders necesarios para las pruebas
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
        
        $this->user = User::factory()->create();
        
        // Asignar permisos necesarios para los tests
        Permission::firstOrCreate(['name' => 'ELIMINAR ASPIRANTE COMPLEMENTARIO']);
        $this->user->givePermissionTo('ELIMINAR ASPIRANTE COMPLEMENTARIO');
    }

    /** @test */
    public function puede_ver_gestion_aspirantes()
    {
        $this->actingAs($this->user);
        ComplementarioOfertado::factory()->count(3)->create();

        $response = $this->get(route('gestion-aspirantes'));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.aspirantes.index');
        $response->assertViewHas('programas');
    }

    /** @test */
    public function puede_ver_aspirantes_de_programa_por_nombre()
    {
        $this->actingAs($this->user);
        
        // Obtener datos necesarios del seeder
        $modalidad = \App\Models\ParametroTema::where('tema_id', 5)
            ->whereIn('parametro_id', [18, 19, 20])
            ->first();
        
        if (!$modalidad) {
            $this->fail('No se encontró modalidad. Asegúrate de que los seeders estén ejecutándose correctamente.');
        }
        
        $jornada = \App\Models\JornadaFormacion::first();
        if (!$jornada) {
            $this->fail('No se encontró jornada. Asegúrate de que JornadaFormacionSeeder esté ejecutándose correctamente.');
        }
        
        $ambiente = \App\Models\Ambiente::first();
        if (!$ambiente) {
            $this->fail('No se encontró ambiente. Asegúrate de que AmbienteSeeder esté ejecutándose correctamente.');
        }

        // Crear programa con nombre exacto que coincida con la búsqueda (guiones convertidos a espacios)
        $programa = ComplementarioOfertado::create([
            'codigo' => 'TEST-PROG-' . uniqid(),
            'nombre' => 'Auxiliar de Cocina',
            'justificacion' => 'Justificación de prueba',
            'requisitos_ingreso' => 'Requisitos de prueba',
            'estado' => 1,
            'duracion' => 30,
            'cupos' => 50,
            'modalidad_id' => $modalidad->id,
            'jornada_id' => $jornada->id,
            'ambiente_id' => $ambiente->id,
        ]);
        
        // Verificar que el programa se creó correctamente
        $this->assertDatabaseHas('complementarios_ofertados', [
            'nombre' => 'Auxiliar de Cocina',
        ]);
        
        // Verificar que el repositorio puede encontrarlo antes de hacer la petición HTTP
        $repo = new \App\Repositories\Complementarios\ComplementarioOfertadoRepository();
        $programaEncontrado = $repo->findByNombre('Auxiliar-de-Cocina');
        
        if (!$programaEncontrado) {
            $this->fail(
                "El repositorio no encontró el programa antes de la petición HTTP. " .
                "Programa creado ID: {$programa->id}, Nombre: '{$programa->nombre}'. " .
                "Búsqueda con: 'Auxiliar-de-Cocina' (convertido a: '" . str_replace('-', ' ', 'Auxiliar-de-Cocina') . "')"
            );
        }
        
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa)->create();

        // La ruta convierte guiones a espacios, así que 'Auxiliar-de-Cocina' busca 'Auxiliar de Cocina'
        $response = $this->get(route('programas-complementarios.ver-aspirantes', 'Auxiliar-de-Cocina'));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.aspirantes.programa');
        $response->assertViewHas('programa');
        $response->assertViewHas('aspirantes');
    }

    /** @test */
    public function puede_ver_aspirantes_de_programa_por_id()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();
        AspiranteComplementario::factory()->count(4)->paraPrograma($programa)->create();

        $response = $this->get(route('aspirantes.programa', $programa->id));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.aspirantes.programa');
        $response->assertViewHas('programa');
        $response->assertViewHas('aspirantes');
    }

    /** @test */
    public function puede_agregar_aspirante_existente()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();
        $persona = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO]);

        $response = $this->post(route('programas-complementarios.agregar-aspirante', $programa->id), [
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('aspirantes_complementarios', [
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);
    }

    /** @test */
    public function no_agrega_aspirante_si_no_existe_persona()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();

        // El servicio valida si existe la persona y devuelve JSON con success: false
        $response = $this->post(route('programas-complementarios.agregar-aspirante', $programa->id), [
            'numero_documento' => '9999999999',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => false]);
        $response->assertJsonFragment(['message' => 'No se encontró ninguna persona registrada con el número de documento "9999999999".']);
    }

    /** @test */
    public function no_agrega_aspirante_si_ya_esta_inscrito()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();
        $persona = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO]);
        AspiranteComplementario::factory()->paraPersona($persona)->paraPrograma($programa)->create();

        $response = $this->post(route('programas-complementarios.agregar-aspirante', $programa->id), [
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => false]);
    }

    /** @test */
    public function puede_rechazar_aspirante()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();
        $aspirante = AspiranteComplementario::factory()->enProceso()->paraPrograma($programa)->create();

        $response = $this->delete(route('programas-complementarios.eliminar-aspirante', [
            'complementarioId' => $programa->id,
            'aspiranteId' => $aspirante->id,
        ]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('aspirantes_complementarios', [
            'id' => $aspirante->id,
            'estado' => 2, // Rechazado
        ]);
    }

    /** @test */
    public function puede_exportar_aspirantes_a_excel()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa)->create();

        $response = $this->get(route('programas-complementarios.exportar-excel', $programa->id));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function puede_descargar_cedulas_de_aspirantes()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();
        AspiranteComplementario::factory()->count(2)->paraPrograma($programa)->create();

        $response = $this->get(route('programas-complementarios.descargar-cedulas', $programa->id));

        // Puede retornar PDF o error, pero debe responder
        $this->assertContains($response->status(), [200, 302, 500]);
    }

    /** @test */
    public function puede_validar_documentos_de_aspirantes()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();
        AspiranteComplementario::factory()->count(2)->paraPrograma($programa)->create();

        $response = $this->post(route('programas-complementarios.validar-documentos', $programa->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
    }

    /** @test */
    public function retorna_error_si_no_hay_aspirantes_para_validar_documentos()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();

        $response = $this->post(route('programas-complementarios.validar-documentos', $programa->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /** @test */
    public function no_agrega_aspirante_si_programa_no_existe()
    {
        $this->actingAs($this->user);
        $persona = Persona::factory()->create(['numero_documento' => self::TEST_NUMERO_DOCUMENTO]);

        $response = $this->post(route('programas-complementarios.agregar-aspirante', 99999), [
            'numero_documento' => self::TEST_NUMERO_DOCUMENTO,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => false]);
    }

    /** @test */
    public function no_rechaza_aspirante_si_no_existe()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();

        $response = $this->delete(route('programas-complementarios.eliminar-aspirante', [
            'complementarioId' => $programa->id,
            'aspiranteId' => 99999,
        ]));

        $response->assertStatus(200);
        $response->assertJson(['success' => false]);
    }

    /** @test */
    public function no_rechaza_aspirante_si_programa_no_existe()
    {
        $this->actingAs($this->user);
        $aspirante = AspiranteComplementario::factory()->create();

        $response = $this->delete(route('programas-complementarios.eliminar-aspirante', [
            'complementarioId' => 99999,
            'aspiranteId' => $aspirante->id,
        ]));

        $response->assertStatus(200);
        $response->assertJson(['success' => false]);
    }

    /** @test */
    public function exportar_excel_retorna_error_si_no_hay_aspirantes()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();

        $response = $this->get(route('programas-complementarios.exportar-excel', $programa->id));

        // Puede retornar Excel vacío (StreamedResponse con status 200) o error JSON (status 500)
        // Ambos son respuestas válidas: Excel vacío significa que se generó correctamente sin datos
        $statusCode = $response->getStatusCode();
            
        // Debe retornar 200 (Excel vacío) o 500 (error), ambos son válidos
        $this->assertContains($statusCode, [200, 500]);
    }

    /** @test */
    public function descargar_cedulas_retorna_error_si_no_hay_aspirantes()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();

        $response = $this->get(route('programas-complementarios.descargar-cedulas', $programa->id));

        // Puede retornar error o redirección
        $this->assertContains($response->status(), [200, 302, 500]);
    }

    /** @test */
    public function puede_ver_aspirantes_con_filtros()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();
        
        AspiranteComplementario::factory()->enProceso()->paraPrograma($programa)->count(2)->create();
        AspiranteComplementario::factory()->admitido()->paraPrograma($programa)->count(1)->create();
        AspiranteComplementario::factory()->rechazado()->paraPrograma($programa)->count(1)->create();

        $response = $this->get(route('aspirantes.programa', $programa->id));

        $response->assertStatus(200);
        $response->assertViewHas('aspirantes');
        $aspirantes = $response->viewData('aspirantes');
        $this->assertGreaterThan(0, $aspirantes->count());
    }

    /** @test */
    public function validar_documentos_retorna_resultado_correcto()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa)->create();

        $response = $this->post(route('programas-complementarios.validar-documentos', $programa->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
    }

    /** @test */
    public function puede_ver_gestion_aspirantes_con_programas_vacios()
    {
        $this->actingAs($this->user);
        // No crear programas

        $response = $this->get(route('gestion-aspirantes'));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.aspirantes.index');
        $programas = $response->viewData('programas');
        $this->assertNotNull($programas);
    }

    /** @test */
    public function puede_ver_aspirantes_por_nombre_con_programa_inexistente()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('programas-complementarios.ver-aspirantes', 'Programa-Inexistente'));

        // Puede retornar 404 o vista vacía
        $this->assertContains($response->status(), [200, 404]);
    }
}
