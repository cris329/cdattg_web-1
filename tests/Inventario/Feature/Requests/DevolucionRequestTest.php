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
        $this->validarCampoRequerido('detalle_orden_id');
    }

    #[Test]
    public function valida_detalle_orden_id_debe_ser_integer(): void
    {
        $this->validarTipoCampo('detalle_orden_id', 'no es numero');
    }

    #[Test]
    public function valida_detalle_orden_id_debe_existir(): void
    {
        $this->validarExistenciaRelacion('detalle_orden_id', self::ID_INEXISTENTE);
    }

    #[Test]
    public function valida_cantidad_devuelta_requerida(): void
    {
        $this->validarCampoRequeridoConDatosBase('cantidad_devuelta', ['detalle_orden_id' => 1]);
    }

    #[Test]
    public function valida_cantidad_devuelta_debe_ser_integer(): void
    {
        $this->validarTipoCampoConDatosBase('cantidad_devuelta', 'no es numero');
    }

    #[Test]
    public function valida_cantidad_devuelta_minima(): void
    {
        $detalleOrden = DetalleOrden::factory()->create();
        $this->validarCampoConDatosCompletos(
            'cantidad_devuelta',
            self::CANTIDAD_DEVUELTA_INVALIDA,
            $detalleOrden->id
        );
    }

    #[Test]
    public function valida_longitud_maxima_de_observaciones(): void
    {
        $detalleOrden = DetalleOrden::factory()->create();
        $this->validarLongitudMaxima(
            'observaciones',
            self::LONGITUD_MAX_OBSERVACIONES,
            $detalleOrden->id
        );
    }

    #[Test]
    public function acepta_datos_validos(): void
    {
        $detalleOrden = DetalleOrden::factory()->create();
        $datos = [
            'detalle_orden_id' => $detalleOrden->id,
            'cantidad_devuelta' => self::CANTIDAD_DEVUELTA_VALIDA,
            'observaciones' => 'Observaciones de la devolución',
        ];

        $this->assertValidacionExitosa($datos);
    }

    #[Test]
    public function acepta_observaciones_nulas(): void
    {
        $detalleOrden = DetalleOrden::factory()->create();
        $datos = [
            'detalle_orden_id' => $detalleOrden->id,
            'cantidad_devuelta' => self::CANTIDAD_DEVUELTA_VALIDA,
            'observaciones' => null,
        ];

        $this->assertValidacionExitosa($datos);
    }

    /**
     * Validate that a field is required.
     */
    private function validarCampoRequerido(string $campo): void
    {
        $rules = $this->obtenerRules();
        $this->validarYVerificarError([], $rules, $campo);
    }

    /**
     * Validate field type.
     */
    private function validarTipoCampo(string $campo, mixed $valorInvalido): void
    {
        $rules = $this->obtenerRules();
        $this->validarYVerificarError([$campo => $valorInvalido], $rules, $campo);
    }

    /**
     * Validate existence of a related entity.
     */
    private function validarExistenciaRelacion(string $campo, int $idInexistente): void
    {
        $rules = $this->obtenerRules();
        $this->validarYVerificarError([$campo => $idInexistente], $rules, $campo);
    }

    /**
     * Validate required field with base data.
     */
    private function validarCampoRequeridoConDatosBase(string $campo, array $datosBase): void
    {
        $rules = $this->obtenerRules();
        $this->validarYVerificarError($datosBase, $rules, $campo);
    }

    /**
     * Validate field type with base data.
     */
    private function validarTipoCampoConDatosBase(string $campo, mixed $valorInvalido): void
    {
        $rules = $this->obtenerRules();
        $datos = [
            'detalle_orden_id' => 1,
            $campo => $valorInvalido,
        ];
        $this->validarYVerificarError($datos, $rules, $campo);
    }

    /**
     * Validate field with complete base data (detalle_orden_id + cantidad_devuelta).
     */
    private function validarCampoConDatosCompletos(string $campo, mixed $valorInvalido, int $detalleOrdenId): void
    {
        $rules = $this->obtenerRules();
        $datos = [
            'detalle_orden_id' => $detalleOrdenId,
            $campo => $valorInvalido,
        ];
        $this->validarYVerificarError($datos, $rules, $campo);
    }

    /**
     * Validate maximum length of a field with complete base data.
     */
    private function validarLongitudMaxima(string $campo, int $longitudMaxima, int $detalleOrdenId): void
    {
        $rules = $this->obtenerRules();
        $datos = [
            'detalle_orden_id' => $detalleOrdenId,
            'cantidad_devuelta' => 1,
            $campo => str_repeat('a', $longitudMaxima),
        ];
        $this->validarYVerificarError($datos, $rules, $campo);
    }

    /**
     * Assert that validation passes with given data.
     */
    private function assertValidacionExitosa(array $datos): void
    {
        $rules = $this->obtenerRules();
        $validator = Validator::make($datos, $rules);
        $this->assertFalse($validator->fails());
    }
}

