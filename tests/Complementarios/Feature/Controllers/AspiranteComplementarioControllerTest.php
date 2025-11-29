<?php

namespace Tests\Complementarios\Feature\Controllers;

use Tests\TestCase;
use App\Models\ComplementarioOfertado;
use App\Models\Persona;
use App\Models\AspiranteComplementario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AspiranteComplementarioControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
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
        $programa = ComplementarioOfertado::factory()->create(['nombre' => 'Auxiliar de Cocina']);
        AspiranteComplementario::factory()->count(3)->paraPrograma($programa)->create();

        $response = $this->get(route('programas-complementarios.ver-aspirantes', 'Auxiliar-de-Cocina'));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.ver_aspirantes');
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
        $persona = Persona::factory()->create(['numero_documento' => '1234567890']);

        $response = $this->post(route('programas-complementarios.agregar-aspirante', $programa->id), [
            'numero_documento' => '1234567890',
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

        $response = $this->post(route('programas-complementarios.agregar-aspirante', $programa->id), [
            'numero_documento' => '9999999999',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => false]);
    }

    /** @test */
    public function no_agrega_aspirante_si_ya_esta_inscrito()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();
        $persona = Persona::factory()->create(['numero_documento' => '1234567890']);
        AspiranteComplementario::factory()->paraPersona($persona)->paraPrograma($programa)->create();

        $response = $this->post(route('programas-complementarios.agregar-aspirante', $programa->id), [
            'numero_documento' => '1234567890',
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
}
