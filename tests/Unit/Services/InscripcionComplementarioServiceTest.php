<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\InscripcionComplementarioService;
use App\Repositories\PersonaRepository;
use App\Repositories\AspiranteComplementarioRepository;
use App\Repositories\ComplementarioOfertadoRepository;
use App\Repositories\TemaRepository;
use App\Services\ComplementarioService;
use App\Services\UserService;
use App\Models\ComplementarioOfertado;
use App\Models\Persona;
use App\Models\AspiranteComplementario;
use App\Models\Pais;
use App\Models\Departamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mockery;

class InscripcionComplementarioServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InscripcionComplementarioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new InscripcionComplementarioService(
            new PersonaRepository(),
            new AspiranteComplementarioRepository(),
            new ComplementarioOfertadoRepository(),
            Mockery::mock(TemaRepository::class),
            Mockery::mock(ComplementarioService::class),
            Mockery::mock(UserService::class)
        );
    }

    /** @test */
    public function puede_preparar_formulario_general()
    {
        Pais::create(['pais' => 'Colombia', 'status' => 1]);
        Departamento::create(['departamento' => 'Cundinamarca', 'pais_id' => 1, 'status' => 1]);

        $data = $this->service->prepararFormularioGeneral();

        $this->assertArrayHasKey('paises', $data);
        $this->assertArrayHasKey('departamentos', $data);
        $this->assertArrayHasKey('tiposDocumento', $data);
        $this->assertArrayHasKey('generos', $data);
    }

    /** @test */
    public function puede_procesar_inscripcion_general()
    {
        $pais = Pais::create(['pais' => 'Colombia', 'status' => 1]);
        $departamento = Departamento::create(['departamento' => 'Cundinamarca', 'pais_id' => $pais->id, 'status' => 1]);
        $municipio = \App\Models\Municipio::create(['municipio' => 'Bogotá', 'departamento_id' => $departamento->id, 'status' => 1]);

        $data = [
            'tipo_documento' => 1,
            'numero_documento' => '1234567890',
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 1,
            'celular' => '3001234567',
            'email' => 'juan@test.com',
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => 'Calle 123',
        ];

        $response = $this->service->procesarInscripcionGeneral($data);

        $this->assertDatabaseHas('personas', [
            'numero_documento' => '1234567890',
            'email' => 'juan@test.com',
        ]);
    }

    /** @test */
    public function no_procesa_inscripcion_general_si_persona_ya_existe()
    {
        $pais = Pais::create(['pais' => 'Colombia', 'status' => 1]);
        $departamento = Departamento::create(['departamento' => 'Cundinamarca', 'pais_id' => $pais->id, 'status' => 1]);
        $municipio = \App\Models\Municipio::create(['municipio' => 'Bogotá', 'departamento_id' => $departamento->id, 'status' => 1]);
        
        $persona = Persona::factory()->create([
            'numero_documento' => '1234567890',
            'email' => 'juan@test.com',
        ]);

        $data = [
            'tipo_documento' => 1,
            'numero_documento' => '1234567890',
            'email' => 'juan@test.com',
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 1,
            'celular' => '3001234567',
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => 'Calle 123',
        ];

        $response = $this->service->procesarInscripcionGeneral($data);

        $this->assertTrue($response->getSession()->has('error'));
    }

    /** @test */
    public function puede_preparar_formulario_inscripcion()
    {
        $programa = ComplementarioOfertado::factory()->create();
        Pais::create(['pais' => 'Colombia', 'status' => 1]);
        Departamento::create(['departamento' => 'Cundinamarca', 'pais_id' => 1, 'status' => 1]);

        $data = $this->service->prepararFormularioInscripcion($programa->id);

        $this->assertArrayHasKey('programa', $data);
        $this->assertEquals($programa->id, $data['programa']->id);
        $this->assertArrayHasKey('paises', $data);
        $this->assertArrayHasKey('departamentos', $data);
    }

    /** @test */
    public function lanza_excepcion_si_programa_no_existe()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $this->service->prepararFormularioInscripcion(99999);
    }

    /** @test */
    public function puede_procesar_inscripcion_a_programa()
    {
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $pais = Pais::create(['pais' => 'Colombia', 'status' => 1]);
        $departamento = Departamento::create(['departamento' => 'Cundinamarca', 'pais_id' => $pais->id, 'status' => 1]);
        $municipio = \App\Models\Municipio::create(['municipio' => 'Bogotá', 'departamento_id' => $departamento->id, 'status' => 1]);
        $programa = ComplementarioOfertado::factory()->conOferta()->create();

        // Crear servicio real sin mocks para este test
        $userService = new \App\Services\UserService();
        $complementarioService = new \App\Services\ComplementarioService(
            Mockery::mock(TemaRepository::class),
            new ComplementarioOfertadoRepository(),
            new AspiranteComplementarioRepository()
        );

        $service = new InscripcionComplementarioService(
            new PersonaRepository(),
            new AspiranteComplementarioRepository(),
            new ComplementarioOfertadoRepository(),
            Mockery::mock(TemaRepository::class),
            $complementarioService,
            $userService
        );

        $data = [
            'tipo_documento' => 1,
            'numero_documento' => '1234567890',
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 1,
            'celular' => '3001234567',
            'email' => 'juan@test.com',
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => 'Calle 123',
            'acepto_privacidad' => '1',
            'acepto_terminos' => '1',
        ];

        $response = $service->procesarInscripcion($data, $programa->id);

        // Validar que se creó la persona
        $this->assertDatabaseHas('personas', [
            'numero_documento' => '1234567890',
            'email' => 'juan@test.com',
        ]);

        // Validar que se creó el aspirante
        $this->assertDatabaseHas('aspirantes_complementarios', [
            'complementario_id' => $programa->id,
        ]);

        // Validar que se creó el usuario
        $persona = Persona::where('numero_documento', '1234567890')->first();
        $this->assertDatabaseHas('users', [
            'email' => 'juan@test.com',
            'persona_id' => $persona->id,
        ]);

        // Validar redirect
        $this->assertTrue($response->isRedirect(route('login.index')));
        $this->assertTrue($response->getSession()->has('success'));
    }

    /** @test */
    public function no_procesa_inscripcion_si_usuario_ya_esta_inscrito()
    {
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
        ]);

        $pais = Pais::create(['pais' => 'Colombia', 'status' => 1]);
        $departamento = Departamento::create(['departamento' => 'Cundinamarca', 'pais_id' => $pais->id, 'status' => 1]);
        $municipio = \App\Models\Municipio::create(['municipio' => 'Bogotá', 'departamento_id' => $departamento->id, 'status' => 1]);
        $programa = ComplementarioOfertado::factory()->conOferta()->create();

        $persona = Persona::factory()->create([
            'numero_documento' => '1234567890',
            'email' => 'juan@test.com',
        ]);

        $user = \App\Models\User::factory()->create([
            'email' => 'juan@test.com',
            'persona_id' => $persona->id,
        ]);

        AspiranteComplementario::factory()->create([
            'persona_id' => $persona->id,
            'complementario_id' => $programa->id,
        ]);

        $this->actingAs($user);

        $data = [
            'tipo_documento' => 1,
            'numero_documento' => '1234567890',
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 1,
            'celular' => '3001234567',
            'email' => 'juan@test.com',
            'pais_id' => $pais->id,
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'direccion' => 'Calle 123',
        ];

        $response = $this->service->procesarInscripcion($data, $programa->id);

        $this->assertTrue($response->getSession()->has('error'));
    }
}
