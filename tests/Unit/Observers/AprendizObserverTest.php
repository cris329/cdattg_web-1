<?php

namespace Tests\Unit\Observers;

use App\Models\Aprendiz;
use App\Models\User;
use App\Observers\AprendizObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AprendizObserverTest extends TestCase
{
    use RefreshDatabase;

    protected AprendizObserver $observer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        $this->observer = new AprendizObserver;
    }

    #[Test]
    public function asigna_rol_aprendiz_al_crear(): void
    {
        $persona = \App\Models\Persona::factory()->create();
        $user = User::factory()->create(['persona_id' => $persona->id]);
        $role = Role::firstOrCreate(['name' => 'APRENDIZ']);

        Aprendiz::factory()->create(['persona_id' => $persona->id]);

        $user->refresh();
        $this->assertTrue($user->hasRole('APRENDIZ'));
    }
}
