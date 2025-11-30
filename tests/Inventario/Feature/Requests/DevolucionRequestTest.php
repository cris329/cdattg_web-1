<?php

declare(strict_types=1);

namespace Tests\Inventario\Feature\Request;

use Tests\TestCase;
use App\Http\Requests\Inventario\DevolucionRequest;
use App\Models\Inventario\DetalleOrden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;

class DevolucionRequestTest extends TestCase
{
    use RefreshDatabase;

    private const ID_INEXISTENTE = 99999;
    private const LONGITUD_MAX_OBSERVACIONES = 501;
    private const CANTIDAD_DEVUELTA_VALIDA = 5;
    private const CANTIDAD_DEVUELTA_INVALIDA = -1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateDatabases();

        // DetalleOrden necesita
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
            \Database\Seeders\SedeSeeder::class,
            \Database\Seeders\BloqueSeeder::class,
            \Database\Seeders\PisoSeeder::class,
            \Database\Seeders\AmbienteSeeder::class,
        ]);
    }

    private function obtenerRules(): array
    {
        $request = new DevolucionRequest();
        return $request->rules();
    }

    private function validarYVerificarError(array $data, array $rules, string $campoEsperado): void
    {
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey($campoEsperado, $validator->errors()->toArray());
    }

    #[Test]
    public function valida_detalle_orden_id_requerido(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError([], $rules, 'detalle_orden_id');
    }

    #[Test]
    public function valida_detalle_orden_id_debe_ser_integer(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            ['detalle_orden_id' => 'no es numero'],
            $rules,
            'detalle_orden_id'
        );
    }

    #[Test]
    public function valida_detalle_orden_id_debe_existir(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            ['detalle_orden_id' => self::ID_INEXISTENTE],
            $rules,
            'detalle_orden_id'
        );
    }

    #[Test]
    public function valida_cantidad_devuelta_requerida(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            ['detalle_orden_id' => 1],
            $rules,
            'cantidad_devuelta'
        );
    }

    #[Test]
    public function valida_cantidad_devuelta_debe_ser_integer(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'detalle_orden_id' => 1,
                'cantidad_devuelta' => 'no es numero',
            ],
            $rules,
            'cantidad_devuelta'
        );
    }

    #[Test]
    public function valida_cantidad_devuelta_minima(): void
    {
        $detalleOrden = DetalleOrden::factory()->create();
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'detalle_orden_id' => $detalleOrden->id,
                'cantidad_devuelta' => self::CANTIDAD_DEVUELTA_INVALIDA,
            ],
            $rules,
            'cantidad_devuelta'
        );
    }

    #[Test]
    public function valida_longitud_maxima_de_observaciones(): void
    {
        $detalleOrden = DetalleOrden::factory()->create();
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            [
                'detalle_orden_id' => $detalleOrden->id,
                'cantidad_devuelta' => 1,
                'observaciones' => str_repeat('a', self::LONGITUD_MAX_OBSERVACIONES),
            ],
            $rules,
            'observaciones'
        );
    }

    #[Test]
    public function acepta_datos_validos(): void
    {
        $detalleOrden = DetalleOrden::factory()->create();
        $rules = $this->obtenerRules();

        $validator = Validator::make([
            'detalle_orden_id' => $detalleOrden->id,
            'cantidad_devuelta' => self::CANTIDAD_DEVUELTA_VALIDA,
            'observaciones' => 'Observaciones de la devolución',
        ], $rules);

        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function acepta_observaciones_nulas(): void
    {
        $detalleOrden = DetalleOrden::factory()->create();
        $rules = $this->obtenerRules();

        $validator = Validator::make([
            'detalle_orden_id' => $detalleOrden->id,
            'cantidad_devuelta' => self::CANTIDAD_DEVUELTA_VALIDA,
            'observaciones' => null,
        ], $rules);

        $this->assertFalse($validator->fails());
    }
}

