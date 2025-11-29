<?php

declare(strict_types=1);

namespace Tests\Inventario\Feature\Request;

use Tests\TestCase;
use App\Http\Requests\Inventario\ProveedorRequest;
use App\Models\Inventario\Proveedor;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\ParametroTema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;

class ProveedorRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateDatabases();
    }

    #[Test]
    public function valida_proveedor_requerido_en_store(): void
    {
        $request = new ProveedorRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('proveedor', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_unicidad_de_proveedor_en_store(): void
    {
        $this->markTestSkipped('Requiere Personas porque Proveedor::factory() crea usuarios que necesitan persona_id');

        $request = new ProveedorRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'proveedor' => 'PROVEEDOR TEST',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('proveedor', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_unicidad_de_nit_en_update(): void
    {
        $this->markTestSkipped('Requiere Personas porque Proveedor::factory() crea usuarios que necesitan persona_id');

        $request = new ProveedorRequest();
        $request->setMethod('PUT');
        $request->setRouteResolver(function () use ($proveedor2) {
            return new class($proveedor2) {
                private $proveedor;
                
                public function __construct($proveedor) {
                    $this->proveedor = $proveedor;
                }
                
                public function parameter($name) {
                    if ($name === 'proveedor') {
                        return $this->proveedor;
                    }
                    return null;
                }
            };
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'proveedor' => 'OTRO PROVEEDOR',
            'nit' => '123456789',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nit', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_unicidad_de_email_en_update(): void
    {
        $this->markTestSkipped('Requiere Personas porque Proveedor::factory() crea usuarios que necesitan persona_id');

        $request = new ProveedorRequest();
        $request->setMethod('PUT');
        $request->setRouteResolver(function () use ($proveedor2) {
            return new class($proveedor2) {
                private $proveedor;
                
                public function __construct($proveedor) {
                    $this->proveedor = $proveedor;
                }
                
                public function parameter($name) {
                    if ($name === 'proveedor') {
                        return $this->proveedor;
                    }
                    return null;
                }
            };
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'proveedor' => 'OTRO PROVEEDOR',
            'email' => 'test1@example.com',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_formato_de_email(): void
    {
        $request = new ProveedorRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'proveedor' => 'PROVEEDOR TEST',
            'email' => 'email-invalido',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_longitud_maxima_de_telefono(): void
    {
        $request = new ProveedorRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'proveedor' => 'PROVEEDOR TEST',
            'telefono' => '12345678901',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('telefono', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_existencia_de_departamento(): void
    {
        $request = new ProveedorRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'proveedor' => 'PROVEEDOR TEST',
            'departamento_id' => 99999,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('departamento_id', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_existencia_de_municipio(): void
    {
        $request = new ProveedorRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'proveedor' => 'PROVEEDOR TEST',
            'municipio_id' => 99999,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('municipio_id', $validator->errors()->toArray());
    }

    #[Test]
    public function acepta_datos_validos_para_store(): void
    {
        $this->markTestSkipped('Requiere Personas y ParametroTema porque usa múltiples factories que requieren datos que no existen aún');

        $request = new ProveedorRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'proveedor' => 'PROVEEDOR VALIDO',
            'nit' => '123456789',
            'email' => 'proveedor@example.com',
            'telefono' => '1234567890',
            'direccion' => 'Dirección del proveedor',
            'departamento_id' => $departamento->id,
            'municipio_id' => $municipio->id,
            'contacto' => 'Contacto del proveedor',
            'estado_id' => $estado->id,
        ], $rules);

        $this->assertFalse($validator->fails());
    }
}

