<?php

namespace Tests\Unit;

use App\Models\EntradaSalida;
use App\Models\FichaCaracterizacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EntradaSalidaModelTest extends TestCase
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
    public function tiene_relacion_con_instructor(): void
    {
        $user = User::factory()->create();
        $entradaSalida = EntradaSalida::factory()->create(['instructor_user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $entradaSalida->instructor);
        $this->assertEquals($user->id, $entradaSalida->instructor->id);
    }

    #[Test]
    public function tiene_relacion_con_ficha_caracterizacion(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        $entradaSalida = EntradaSalida::factory()->create(['ficha_caracterizacion_id' => $ficha->id]);

        $this->assertInstanceOf(FichaCaracterizacion::class, $entradaSalida->fichaCaracterizacion);
        $this->assertEquals($ficha->id, $entradaSalida->fichaCaracterizacion->id);
    }

    #[Test]
    public function puede_crear_registro_con_datos_validos(): void
    {
        $datos = [
            'fecha' => now()->format('Y-m-d'),
            'entrada' => '08:00:00',
            'salida' => '18:00:00',
        ];

        $entradaSalida = EntradaSalida::factory()->create($datos);

        $this->assertDatabaseHas('entrada_salidas', [
            'id' => $entradaSalida->id,
        ]);
    }
}

