<?php

namespace Tests\Unit\Observers;

use App\Models\Instructor;
use App\Models\User;
use App\Observers\InstructorObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InstructorObserverTest extends TestCase
{
    use RefreshDatabase;

    protected InstructorObserver $observer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
        ]);

        $this->observer = new InstructorObserver;
    }

    #[Test]
    public function asigna_rol_instructor_al_crear(): void
    {
        $persona = \App\Models\Persona::factory()->create();
        $user = User::factory()->create(['persona_id' => $persona->id]);
        $role = Role::firstOrCreate(['name' => 'INSTRUCTOR']);

        Instructor::factory()->create(['persona_id' => $persona->id]);

        $user->refresh();
        $this->assertTrue($user->hasRole('INSTRUCTOR'));
    }
}
