<?php

namespace Tests\Unit;

use App\Models\JornadaFormacion;
use App\Repositories\JornadaFormacionRepository;
use App\Services\JornadaFormacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JornadaFormacionServiceTest extends TestCase
{
    use RefreshDatabase;

    private JornadaFormacionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->service = new JornadaFormacionService(
            app(JornadaFormacionRepository::class)
        );
    }

    #[Test]
    public function lista_todas_las_jornadas(): void
    {
        JornadaFormacion::factory()->count(3)->create();

        $resultado = $this->service->listarTodas();

        $this->assertGreaterThanOrEqual(3, $resultado->count());
    }

    #[Test]
    public function obtiene_jornadas_activas(): void
    {
        JornadaFormacion::factory()->count(2)->create(['status' => true]);
        JornadaFormacion::factory()->count(1)->create(['status' => false]);

        $resultado = $this->service->obtenerActivas();

        $this->assertGreaterThanOrEqual(2, $resultado->count());
        foreach ($resultado as $jornada) {
            $this->assertEquals(1, $jornada->status);
        }
    }

    #[Test]
    public function crea_jornada(): void
    {
        $datos = [
            'nombre' => 'Diurna',
            'hora_inicio' => '06:00:00',
            'hora_fin' => '18:00:00',
            'status' => true,
        ];

        $jornada = $this->service->crear($datos);

        $this->assertDatabaseHas('jornadas_formacion', [
            'nombre' => 'Diurna',
        ]);
        $this->assertEquals('Diurna', $jornada->nombre);
    }

    #[Test]
    public function actualiza_jornada(): void
    {
        $jornada = JornadaFormacion::factory()->create();

        $actualizado = $this->service->actualizar($jornada->id, ['nombre' => 'Actualizada']);

        $this->assertTrue($actualizado);
        $this->assertDatabaseHas('jornadas_formacion', [
            'id' => $jornada->id,
            'nombre' => 'Actualizada',
        ]);
    }

    #[Test]
    public function elimina_jornada(): void
    {
        $jornada = JornadaFormacion::factory()->create();

        $eliminado = $this->service->eliminar($jornada->id);

        $this->assertTrue($eliminado);
        $this->assertDatabaseMissing('jornadas_formacion', [
            'id' => $jornada->id,
        ]);
    }

    #[Test]
    public function cambia_estado_jornada(): void
    {
        $jornada = JornadaFormacion::factory()->create(['status' => true]);

        $cambiado = $this->service->cambiarEstado($jornada->id);

        $this->assertTrue($cambiado);
    }

    #[Test]
    public function valida_horarios_correctos(): void
    {
        $valido = $this->service->validarHorarios('08:00:00', '18:00:00');

        $this->assertTrue($valido);
    }

    #[Test]
    public function valida_horarios_incorrectos(): void
    {
        $valido = $this->service->validarHorarios('18:00:00', '08:00:00');

        $this->assertFalse($valido);
    }
}

