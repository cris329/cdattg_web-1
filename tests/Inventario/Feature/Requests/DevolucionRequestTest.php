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

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateDatabases();

        // DetalleOrden necesita: Orden, Producto → Ambiente → Piso → Bloque → Sede → Regional
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

    #[Test]
    public function valida_detalle_orden_id_requerido(): void
    {
        $request = new DevolucionRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('detalle_orden_id', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_detalle_orden_id_debe_ser_integer(): void
    {
        $request = new DevolucionRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'detalle_orden_id' => 'no es numero',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('detalle_orden_id', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_detalle_orden_id_debe_existir(): void
    {
        $request = new DevolucionRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'detalle_orden_id' => 99999,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('detalle_orden_id', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_cantidad_devuelta_requerida(): void
    {
        $request = new DevolucionRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'detalle_orden_id' => 1,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cantidad_devuelta', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_cantidad_devuelta_debe_ser_integer(): void
    {
        $request = new DevolucionRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'detalle_orden_id' => 1,
            'cantidad_devuelta' => 'no es numero',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cantidad_devuelta', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_cantidad_devuelta_minima(): void
    {
        $detalleOrden = DetalleOrden::factory()->create();

        $request = new DevolucionRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'detalle_orden_id' => $detalleOrden->id,
            'cantidad_devuelta' => -1,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cantidad_devuelta', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_longitud_maxima_de_observaciones(): void
    {
        $detalleOrden = DetalleOrden::factory()->create();

        $request = new DevolucionRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'detalle_orden_id' => $detalleOrden->id,
            'cantidad_devuelta' => 1,
            'observaciones' => str_repeat('a', 501),
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('observaciones', $validator->errors()->toArray());
    }

    #[Test]
    public function acepta_datos_validos(): void
    {
        $detalleOrden = DetalleOrden::factory()->create();

        $request = new DevolucionRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'detalle_orden_id' => $detalleOrden->id,
            'cantidad_devuelta' => 5,
            'observaciones' => 'Observaciones de la devolución',
        ], $rules);

        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function acepta_observaciones_nulas(): void
    {
        $detalleOrden = DetalleOrden::factory()->create();

        $request = new DevolucionRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'detalle_orden_id' => $detalleOrden->id,
            'cantidad_devuelta' => 5,
            'observaciones' => null,
        ], $rules);

        $this->assertFalse($validator->fails());
    }
}

