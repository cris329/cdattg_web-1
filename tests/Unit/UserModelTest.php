<?php

namespace Tests\Unit;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

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
    }

    #[Test]
    public function tiene_relacion_con_persona(): void
    {
        $persona = Persona::factory()->create();
        $user = User::factory()->create(['persona_id' => $persona->id]);

        $this->assertInstanceOf(Persona::class, $user->persona);
        $this->assertEquals($persona->id, $user->persona->id);
    }

    #[Test]
    public function sincroniza_email_con_persona_al_crear(): void
    {
        $persona = Persona::factory()->create(['email' => null]);
        $user = User::factory()->create([
            'persona_id' => $persona->id,
            'email' => 'test@sena.edu.co',
        ]);

        $persona->refresh();

        $this->assertEquals('test@sena.edu.co', $persona->email);
    }

    #[Test]
    public function sincroniza_email_con_persona_al_actualizar(): void
    {
        $persona = Persona::factory()->create(['email' => 'old@example.com']);
        $user = User::factory()->create([
            'persona_id' => $persona->id,
            'email' => 'old@example.com',
        ]);

        $user->update(['email' => 'new@sena.edu.co']);

        $persona->refresh();

        $this->assertEquals('new@sena.edu.co', $persona->email);
    }

    #[Test]
    public function puede_asignar_roles(): void
    {
        $user = User::factory()->create();
        $rol = Role::firstOrCreate(['name' => 'INSTRUCTOR']);

        $user->assignRole($rol);

        $this->assertTrue($user->hasRole('INSTRUCTOR'));
    }

    #[Test]
    public function puede_verificar_permisos(): void
    {
        $user = User::factory()->create();
        $rol = Role::firstOrCreate(['name' => 'INSTRUCTOR']);
        $permiso = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'VER PERSONA']);
        $rol->givePermissionTo($permiso);

        $user->assignRole($rol);

        $this->assertTrue($user->hasPermissionTo('VER PERSONA'));
    }

    #[Test]
    public function obtiene_nombre_completo_desde_persona(): void
    {
        $persona = Persona::factory()->create([
            'primer_nombre' => 'Juan',
            'segundo_nombre' => 'Carlos',
            'primer_apellido' => 'Pérez',
            'segundo_apellido' => 'González',
        ]);
        $user = User::factory()->create(['persona_id' => $persona->id]);

        $this->assertStringContainsString('Juan', $user->name);
        $this->assertStringContainsString('Pérez', $user->name);
    }
}

