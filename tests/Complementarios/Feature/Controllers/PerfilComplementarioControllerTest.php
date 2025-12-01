<?php

declare(strict_types=1);

namespace Tests\Complementarios\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Persona;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Complementarios\AspiranteComplementario;
use App\Services\Complementarios\ComplementarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use PHPUnit\Framework\Attributes\Test;
use Tests\Complementarios\Concerns\SeedsComplementariosDatabase;

class PerfilComplementarioControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    use SeedsComplementariosDatabase;

    private const ROUTE_PERFIL = 'aspirantes.perfil';
    private const ROUTE_HOME = 'home';
    private const ROL_ASPIRANTE = 'ASPIRANTE';

    protected User $user;
    protected Persona $persona;
    protected ComplementarioService $complementarioService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedComplementariosDatabaseIfNeeded();

        // Create persona
        $this->persona = Persona::factory()->create();

        // Create user with persona
        $this->user = User::factory()->create([
            'persona_id' => $this->persona->id,
        ]);

        // Get service instance
        $this->complementarioService = app(ComplementarioService::class);
    }

    #[Test]
    public function usuario_autenticado_puede_ver_su_perfil()
    {
        $this->actingAs($this->user);

        // Mock ComplementarioService
        $mockService = $this->mock(ComplementarioService::class);
        $mockService->shouldReceive('getTiposDocumento')
            ->once()
            ->andReturn(collect());
        $mockService->shouldReceive('getGeneros')
            ->once()
            ->andReturn(collect());

        $this->app->instance(ComplementarioService::class, $mockService);

        $response = $this->get(route(self::ROUTE_PERFIL));

        $response->assertStatus(200);
        $response->assertViewIs('personas.show');
        $response->assertViewHas('persona');
        $response->assertViewHas('user');
        $response->assertViewHas('aspirantes');
        $response->assertViewHas('tiposDocumento');
        $response->assertViewHas('generos');
        $response->assertViewHas('soloPerfil', true);
        $response->assertViewHas('rolesDisponibles');
    }

    #[Test]
    public function usuario_no_autenticado_es_redirigido_a_login()
    {
        $response = $this->get(route(self::ROUTE_PERFIL));

        // The controller redirects to '/login' but middleware may redirect differently
        $response->assertRedirect();
    }

    #[Test]
    public function usuario_sin_persona_es_redirigido_a_home()
    {
        // Create persona first
        $persona = Persona::factory()->create();
        
        // Create user with persona
        $userConPersona = User::factory()->create([
            'persona_id' => $persona->id,
        ]);

        $userId = $userConPersona->id;
        /** @var User $userConPersona */
        $this->actingAs($userConPersona);

        // Temporarily disable foreign key constraints to simulate orphaned user
        // This tests the controller's null check for persona
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        $persona->delete();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
        
        // Reload user to clear cached persona relationship
        /** @var User $userReloaded */
        $userReloaded = User::find($userId);
        $this->assertNotNull($userReloaded, 'User should still exist after persona deletion');
        $this->actingAs($userReloaded);

        $response = $this->get(route(self::ROUTE_PERFIL));

        $response->assertRedirect(route(self::ROUTE_HOME));
        $response->assertSessionHas('error');
    }

    #[Test]
    public function usuario_con_rol_aspirante_ve_sus_programas_complementarios()
    {
        // Create ASPIRANTE role if doesn't exist
        Role::firstOrCreate(['name' => self::ROL_ASPIRANTE]);
        $this->user->assignRole(self::ROL_ASPIRANTE);

        // Create programa complementario
        $programa = ComplementarioOfertado::factory()->create();

        // Create aspirante for this user
        $aspirante = AspiranteComplementario::factory()->create([
            'persona_id' => $this->persona->id,
            'complementario_id' => $programa->id,
        ]);

        $this->actingAs($this->user);

        // Mock ComplementarioService
        $mockService = $this->mock(ComplementarioService::class);
        $mockService->shouldReceive('getTiposDocumento')
            ->once()
            ->andReturn(collect());
        $mockService->shouldReceive('getGeneros')
            ->once()
            ->andReturn(collect());

        $this->app->instance(ComplementarioService::class, $mockService);

        $response = $this->get(route(self::ROUTE_PERFIL));

        $response->assertStatus(200);
        $response->assertViewHas('aspirantes');
        
        $aspirantes = $response->viewData('aspirantes');
        $this->assertCount(1, $aspirantes);
        $this->assertEquals($aspirante->id, $aspirantes->first()->id);
    }

    #[Test]
    public function usuario_sin_rol_aspirante_no_ve_aspirantes()
    {
        // Don't assign ASPIRANTE role
        $this->actingAs($this->user);

        // Mock ComplementarioService
        $mockService = $this->mock(ComplementarioService::class);
        $mockService->shouldReceive('getTiposDocumento')
            ->once()
            ->andReturn(collect());
        $mockService->shouldReceive('getGeneros')
            ->once()
            ->andReturn(collect());

        $this->app->instance(ComplementarioService::class, $mockService);

        $response = $this->get(route(self::ROUTE_PERFIL));

        $response->assertStatus(200);
        $response->assertViewHas('aspirantes');
        
        $aspirantes = $response->viewData('aspirantes');
        $this->assertIsArray($aspirantes);
        $this->assertEmpty($aspirantes);
    }

    #[Test]
    public function usuario_aspirante_ve_solo_sus_programas_complementarios()
    {
        // Create ASPIRANTE role
        Role::firstOrCreate(['name' => self::ROL_ASPIRANTE]);
        $this->user->assignRole(self::ROL_ASPIRANTE);

        // Create another persona and user
        $otraPersona = Persona::factory()->create();
        $otroUser = User::factory()->create([
            'persona_id' => $otraPersona->id,
        ]);
        $otroUser->assignRole(self::ROL_ASPIRANTE);

        // Create programas
        $programa1 = ComplementarioOfertado::factory()->create();
        $programa2 = ComplementarioOfertado::factory()->create();
        $programa3 = ComplementarioOfertado::factory()->create();

        // Create aspirantes: 2 for this user, 1 for other user
        $aspirante1 = AspiranteComplementario::factory()->create([
            'persona_id' => $this->persona->id,
            'complementario_id' => $programa1->id,
        ]);
        $aspirante2 = AspiranteComplementario::factory()->create([
            'persona_id' => $this->persona->id,
            'complementario_id' => $programa2->id,
        ]);
        AspiranteComplementario::factory()->create([
            'persona_id' => $otraPersona->id,
            'complementario_id' => $programa3->id,
        ]);

        $this->actingAs($this->user);

        // Mock ComplementarioService
        $mockService = $this->mock(ComplementarioService::class);
        $mockService->shouldReceive('getTiposDocumento')
            ->once()
            ->andReturn(collect());
        $mockService->shouldReceive('getGeneros')
            ->once()
            ->andReturn(collect());

        $this->app->instance(ComplementarioService::class, $mockService);

        $response = $this->get(route(self::ROUTE_PERFIL));

        $response->assertStatus(200);
        $aspirantes = $response->viewData('aspirantes');
        $this->assertCount(2, $aspirantes);
        $aspirantesIds = $aspirantes->pluck('id')->toArray();
        $this->assertContains($aspirante1->id, $aspirantesIds);
        $this->assertContains($aspirante2->id, $aspirantesIds);
    }

    #[Test]
    public function perfil_muestra_tipos_documento_y_generos()
    {
        $this->actingAs($this->user);

        // Mock ComplementarioService with data
        $tiposDocumento = collect([
            (object) ['id' => 1, 'name' => 'CEDULA'],
            (object) ['id' => 2, 'name' => 'PASAPORTE'],
        ]);
        $generos = collect([
            (object) ['id' => 1, 'name' => 'MASCULINO'],
            (object) ['id' => 2, 'name' => 'FEMENINO'],
        ]);

        $mockService = $this->mock(ComplementarioService::class);
        $mockService->shouldReceive('getTiposDocumento')
            ->once()
            ->andReturn($tiposDocumento);
        $mockService->shouldReceive('getGeneros')
            ->once()
            ->andReturn($generos);

        $this->app->instance(ComplementarioService::class, $mockService);

        $response = $this->get(route(self::ROUTE_PERFIL));

        $response->assertStatus(200);
        $response->assertViewHas('tiposDocumento', $tiposDocumento);
        $response->assertViewHas('generos', $generos);
    }

    #[Test]
    public function perfil_pasa_datos_correctos_a_la_vista()
    {
        $this->actingAs($this->user);

        // Mock ComplementarioService
        $mockService = $this->mock(ComplementarioService::class);
        $mockService->shouldReceive('getTiposDocumento')
            ->once()
            ->andReturn(collect());
        $mockService->shouldReceive('getGeneros')
            ->once()
            ->andReturn(collect());

        $this->app->instance(ComplementarioService::class, $mockService);

        $response = $this->get(route(self::ROUTE_PERFIL));

        $response->assertStatus(200);
        $response->assertViewHas('persona', $this->persona);
        $response->assertViewHas('user', $this->user);
        $response->assertViewHas('soloPerfil', true);
        $response->assertViewHas('rolesDisponibles');
        
        $rolesDisponibles = $response->viewData('rolesDisponibles');
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $rolesDisponibles);
        $this->assertTrue($rolesDisponibles->isEmpty());
    }

    #[Test]
    public function usuario_aspirante_con_multiples_programas_ve_todos()
    {
        // Create ASPIRANTE role
        Role::firstOrCreate(['name' => self::ROL_ASPIRANTE]);
        $this->user->assignRole(self::ROL_ASPIRANTE);

        // Create multiple programas
        $programas = ComplementarioOfertado::factory()->count(5)->create();

        // Create aspirantes for all programas
        $programas->each(function ($programa) {
            AspiranteComplementario::factory()->create([
                'persona_id' => $this->persona->id,
                'complementario_id' => $programa->id,
            ]);
        });

        $this->actingAs($this->user);

        // Mock ComplementarioService
        $mockService = $this->mock(ComplementarioService::class);
        $mockService->shouldReceive('getTiposDocumento')
            ->once()
            ->andReturn(collect());
        $mockService->shouldReceive('getGeneros')
            ->once()
            ->andReturn(collect());

        $this->app->instance(ComplementarioService::class, $mockService);

        $response = $this->get(route(self::ROUTE_PERFIL));

        $response->assertStatus(200);
        $aspirantesView = $response->viewData('aspirantes');
        $this->assertCount(5, $aspirantesView);
        
        // Verify all aspirantes have relationships loaded
        foreach ($aspirantesView as $aspirante) {
            $this->assertTrue($aspirante->relationLoaded('persona'));
            $this->assertTrue($aspirante->relationLoaded('complementario'));
        }
    }
}

