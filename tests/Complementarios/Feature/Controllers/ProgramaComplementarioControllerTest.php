<?php

namespace Tests\Complementarios\Feature\Controllers;

use Tests\TestCase;
use App\Models\ComplementarioOfertado;
use App\Models\User;
use App\Models\Parametro;
use App\Models\Competencia;
use App\Models\ResultadosAprendizaje;
use App\Models\GuiasAprendizaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class ProgramaComplementarioControllerTest extends TestCase
{
    use RefreshDatabase;

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
    }

    /** @test */
    public function puede_listar_programas_complementarios_admin()
    {
        $this->actingAs($this->user);
        ComplementarioOfertado::factory()->count(5)->create();

        $response = $this->get(route('complementarios-ofertados.index'));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.programas.admin.index');
        $response->assertViewHas('programas');
    }

    /** @test */
    public function puede_ver_formulario_creacion_programa()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('complementarios-ofertados.create'));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.programas.admin.create');
    }

    /** @test */
    public function puede_ver_programas_publicos()
    {
        ComplementarioOfertado::factory()->count(3)->conOferta()->create();
        ComplementarioOfertado::factory()->count(2)->sinOferta()->create();

        $response = $this->get(route('programas-complementarios.index'));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.programas.public.index');
        $response->assertViewHas('programas');
    }

    /** @test */
    public function puede_ver_formulario_edicion_programa()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();

        $response = $this->get(route('complementarios-ofertados.edit', $programa->id));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.programas.admin.edit');
        $response->assertViewHas('programa');
    }

    /** @test */
    public function puede_obtener_datos_programa_para_edicion_api()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();

        $response = $this->get(route('complementarios-ofertados.edit-api', $programa->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'codigo',
            'nombre',
            'justificacion',
            'requisitos_ingreso',
            'duracion',
            'cupos',
            'estado',
            'modalidad_id',
            'jornada_id',
            'ambiente_id',
            'dias',
        ]);
    }

    /** @test */
    public function puede_crear_programa_complementario()
    {
        $this->actingAs($this->user);

        // Obtener datos necesarios del seeder
        $modalidad = \App\Models\ParametroTema::where('tema_id', 5)
            ->whereIn('parametro_id', [18, 19, 20])
            ->first();
        $jornada = \App\Models\JornadaFormacion::first();
        $ambiente = \App\Models\Ambiente::first();

        // Obtener días de la semana del seeder (tema_id 4 es DIAS)
        $dias = \App\Models\ParametroTema::where('tema_id', 4)
            ->whereIn('parametro_id', [12, 13, 14, 15, 16, 17, 18])
            ->take(2)
            ->get();
        
        if ($dias->count() < 2) {
            // Si no hay suficientes días, usar los primeros parámetros de días disponibles
            $parametrosDias = \App\Models\Parametro::whereIn('id', [12, 13, 14, 15, 16, 17, 18])->take(2)->get();
            $dia1 = $parametrosDias->first();
            $dia2 = $parametrosDias->last();
        } else {
            $dia1 = $dias->first()->parametro;
            $dia2 = $dias->last()->parametro;
        }

        $data = [
            'codigo' => 'COMP0001',
            'nombre' => 'Programa Test',
            'justificacion' => 'Justificación de prueba',
            'requisitos_ingreso' => 'Requisitos de prueba',
            'duracion' => 60,
            'cupos' => 30,
            'estado' => 1,
            'modalidad_id' => $modalidad->id,
            'jornada_id' => $jornada->id,
            'ambiente_id' => $ambiente->id,
            'dias' => [
                [
                    'dia_id' => $dia1->id,
                    'hora_inicio' => '08:00',
                    'hora_fin' => '12:00',
                ],
                [
                    'dia_id' => $dia2->id,
                    'hora_inicio' => '14:00',
                    'hora_fin' => '18:00',
                ],
            ],
        ];

        $response = $this->post(route('complementarios-ofertados.store'), $data);

        $response->assertRedirect(route('complementarios-ofertados.index'));
        $response->assertSessionHas('success');
        
        // Validar que se creó el programa
        $this->assertDatabaseHas('complementarios_ofertados', [
            'codigo' => 'COMP0001',
            'nombre' => 'Programa Test',
        ]);

        $programa = ComplementarioOfertado::where('codigo', 'COMP0001')->first();
        
        // Validar que se sincronizaron los días de formación
        $this->assertCount(2, $programa->diasFormacion);
        $this->assertTrue($programa->diasFormacion->contains($dia1->id));
        $this->assertTrue($programa->diasFormacion->contains($dia2->id));
        
        // Validar horas en el pivot
        $dia1Pivot = $programa->diasFormacion->firstWhere('id', $dia1->id)->pivot;
        $this->assertEquals('08:00', $dia1Pivot->hora_inicio);
        $this->assertEquals('12:00', $dia1Pivot->hora_fin);
    }

    /** @test */
    public function puede_crear_programa_complementario_con_estructura_academica()
    {
        $this->actingAs($this->user);

        // Obtener datos necesarios del seeder
        $modalidad = \App\Models\ParametroTema::where('tema_id', 5)
            ->whereIn('parametro_id', [18, 19, 20])
            ->first();
        $jornada = \App\Models\JornadaFormacion::first();
        $ambiente = \App\Models\Ambiente::first();

        // Crear competencia con todos los campos requeridos
        $competencia = Competencia::create([
            'codigo' => 'COMP-' . uniqid(),
            'nombre' => 'Competencia Test',
            'descripcion' => 'Descripción de prueba',
            'duracion' => 40,
            'fecha_inicio' => now()->format('Y-m-d'),
            'fecha_fin' => now()->addMonths(6)->format('Y-m-d'),
            'status' => true,
            'user_create_id' => $this->user->id,
        ]);

        // Crear ResultadosAprendizaje con todos los campos requeridos
        $rap = ResultadosAprendizaje::create([
            'codigo' => 'RAP-' . uniqid(),
            'nombre' => 'Resultado de Aprendizaje Test',
            'duracion' => 20,
            'fecha_inicio' => now()->format('Y-m-d'),
            'fecha_fin' => now()->addMonths(3)->format('Y-m-d'),
            'status' => true,
            'user_create_id' => $this->user->id,
        ]);

        // Crear GuiasAprendizaje con todos los campos requeridos
        $guia = GuiasAprendizaje::create([
            'codigo' => 'GUIA-' . uniqid(),
            'nombre' => 'Guía de Aprendizaje Test',
            'status' => true,
            'user_create_id' => $this->user->id,
        ]);

        $data = [
            'codigo' => 'COMP0002',
            'nombre' => 'Programa con Estructura',
            'justificacion' => 'Justificación de prueba',
            'requisitos_ingreso' => 'Requisitos de prueba',
            'duracion' => 60,
            'cupos' => 30,
            'estado' => 1,
            'modalidad_id' => $modalidad->id,
            'jornada_id' => $jornada->id,
            'ambiente_id' => $ambiente->id,
            'competencias' => [$competencia->id],
            'raps' => [$rap->id],
            'guias' => [$guia->id],
        ];

        $response = $this->post(route('complementarios-ofertados.store'), $data);

        $response->assertRedirect(route('complementarios-ofertados.index'));
        $response->assertSessionHas('success');
        
        $programa = ComplementarioOfertado::where('codigo', 'COMP0002')->first();
        
        // Validar sincronización de estructura académica
        $this->assertTrue($programa->competencias->contains($competencia->id));
        $this->assertTrue($programa->raps->contains($rap->id));
        $this->assertTrue($programa->guiasAprendizaje->contains($guia->id));
    }

    /** @test */
    public function puede_actualizar_programa_complementario()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();

        // Asegurar que el programa tenga ambiente_id válido
        if (!$programa->ambiente_id) {
            $ambiente = \App\Models\Ambiente::first();
            $programa->ambiente_id = $ambiente->id;
            $programa->save();
        }

        $data = [
            'codigo' => $programa->codigo,
            'nombre' => 'Programa Actualizado',
            'justificacion' => 'Nueva justificación',
            'requisitos_ingreso' => 'Nuevos requisitos',
            'duracion' => 80,
            'cupos' => 40,
            'estado' => 1,
            'modalidad_id' => $programa->modalidad_id,
            'jornada_id' => $programa->jornada_id,
            'ambiente_id' => $programa->ambiente_id,
        ];

        $response = $this->put(route('complementarios-ofertados.update', $programa->id), $data);

        $response->assertRedirect(route('complementarios-ofertados.show', $programa->id));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('complementarios_ofertados', [
            'id' => $programa->id,
            'nombre' => 'Programa Actualizado',
        ]);
    }

    /** @test */
    public function puede_eliminar_programa_complementario()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();

        $response = $this->delete(route('complementarios-ofertados.destroy', $programa->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('complementarios_ofertados', [
            'id' => $programa->id,
        ]);
    }

    /** @test */
    public function puede_ver_detalles_programa_admin()
    {
        $this->actingAs($this->user);
        $programa = ComplementarioOfertado::factory()->create();

        $response = $this->get(route('complementarios-ofertados.show', $programa->id));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.programas.admin.show');
        $response->assertViewHas('programa');
    }

    /** @test */
    public function puede_ver_programa_especifico_publico()
    {
        $programa = ComplementarioOfertado::factory()->conOferta()->create();

        $response = $this->get(route('programas-complementarios.show', $programa->id));

        $response->assertStatus(200);
        $response->assertViewIs('complementarios.programas.public.show');
        $response->assertViewHas('programaData');
    }
}
