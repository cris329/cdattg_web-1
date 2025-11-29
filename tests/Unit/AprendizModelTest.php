<?php

namespace Tests\Unit;

use App\Models\Aprendiz;
use App\Models\AprendizFicha;
use App\Models\AsistenciaAprendiz;
use App\Models\FichaCaracterizacion;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AprendizModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
        ]);
    }

    #[Test]
    public function tiene_relacion_con_persona(): void
    {
        $persona = Persona::factory()->create();
        $aprendiz = Aprendiz::factory()->create(['persona_id' => $persona->id]);

        $this->assertInstanceOf(Persona::class, $aprendiz->persona);
        $this->assertEquals($persona->id, $aprendiz->persona->id);
    }

    #[Test]
    public function tiene_relacion_con_ficha_caracterizacion(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $aprendiz = Aprendiz::factory()->create(['ficha_caracterizacion_id' => $ficha->id]);

        $this->assertInstanceOf(FichaCaracterizacion::class, $aprendiz->fichaCaracterizacion);
        $this->assertEquals($ficha->id, $aprendiz->fichaCaracterizacion->id);
    }

    #[Test]
    public function tiene_relacion_muchos_a_muchos_con_fichas(): void
    {
        $aprendiz = Aprendiz::factory()->create();
        $fichas = FichaCaracterizacion::factory()->count(2)->create();

        $aprendiz->fichas()->attach($fichas->pluck('id')->toArray());

        $this->assertCount(2, $aprendiz->fichas);
    }

    #[Test]
    public function tiene_relacion_con_aprendiz_fichas(): void
    {
        $aprendiz = Aprendiz::factory()->create();
        AprendizFicha::factory()->count(2)->create(['aprendiz_id' => $aprendiz->id]);

        $this->assertCount(2, $aprendiz->aprendizFichas);
    }

    #[Test]
    public function tiene_relacion_con_asistencias(): void
    {
        $aprendiz = Aprendiz::factory()->create();
        $aprendizFicha = AprendizFicha::factory()->create(['aprendiz_id' => $aprendiz->id]);
        AsistenciaAprendiz::factory()->create(['aprendiz_ficha_id' => $aprendizFicha->id]);

        $this->assertGreaterThanOrEqual(1, $aprendiz->asistencias->count());
    }

    #[Test]
    public function verifica_rol_directo(): void
    {
        $aprendiz = Aprendiz::factory()->create();
        $rol = Role::firstOrCreate(['name' => 'APRENDIZ']);
        $aprendiz->assignRole($rol);

        $this->assertTrue($aprendiz->hasRole('APRENDIZ'));
    }

    #[Test]
    public function verifica_rol_a_traves_de_usuario(): void
    {
        $persona = Persona::factory()->create();
        $user = User::factory()->create(['persona_id' => $persona->id]);
        $aprendiz = Aprendiz::factory()->create(['persona_id' => $persona->id]);

        $rol = Role::firstOrCreate(['name' => 'APRENDIZ']);
        $user->assignRole($rol);

        $this->assertTrue($aprendiz->hasRole('APRENDIZ'));
    }

    #[Test]
    public function obtiene_todos_los_roles(): void
    {
        $persona = Persona::factory()->create();
        $user = User::factory()->create(['persona_id' => $persona->id]);
        $aprendiz = Aprendiz::factory()->create(['persona_id' => $persona->id]);

        $rol = Role::firstOrCreate(['name' => 'APRENDIZ']);
        $user->assignRole($rol);
        $aprendiz->assignRole($rol);

        $roles = $aprendiz->getAllRoles();

        $this->assertGreaterThanOrEqual(1, $roles->count());
    }
}

