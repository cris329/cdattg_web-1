<?php

declare(strict_types=1);

namespace Tests\Inventario\Feature\Request;

use Tests\TestCase;
use App\Http\Requests\Inventario\MarcaCategoriaRequest;
use App\Models\Parametro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;

class MarcaCategoriaRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateDatabases();
    }

    #[Test]
    public function valida_name_requerido_en_store(): void
    {
        $request = new MarcaCategoriaRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_name_debe_ser_string(): void
    {
        $request = new MarcaCategoriaRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 123,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_unicidad_de_name_en_store(): void
    {
        $parametro = Parametro::factory()->create(['name' => 'MARCA TEST']);

        $request = new MarcaCategoriaRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'MARCA TEST',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_unicidad_de_name_en_update(): void
    {
        $parametro1 = Parametro::factory()->create(['name' => 'MARCA 1']);
        $parametro2 = Parametro::factory()->create(['name' => 'MARCA 2']);

        $request = new MarcaCategoriaRequest();
        $request->setMethod('PUT');
        $request->setRouteResolver(function () use ($parametro2) {
            return new class($parametro2) {
                private $parametro;
                
                public function __construct($parametro) {
                    $this->parametro = $parametro;
                }
                
                public function parameter($name) {
                    if ($name === 'categoria') {
                        return $this->parametro;
                    }
                    if ($name === 'marca') {
                        return null;
                    }
                    return null;
                }
            };
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'MARCA 1',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[Test]
    public function permite_mismo_name_en_update(): void
    {
        $parametro = Parametro::factory()->create(['name' => 'MARCA TEST']);

        $request = new MarcaCategoriaRequest();
        $request->setMethod('PUT');
        $request->setRouteResolver(function () use ($parametro) {
            return new class($parametro) {
                private $parametro;
                
                public function __construct($parametro) {
                    $this->parametro = $parametro;
                }
                
                public function parameter($name) {
                    if ($name === 'categoria') {
                        return $this->parametro;
                    }
                    if ($name === 'marca') {
                        return null;
                    }
                    return null;
                }
            };
        });

        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'MARCA TEST',
        ], $rules);

        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function acepta_datos_validos_para_store(): void
    {
        $request = new MarcaCategoriaRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'NUEVA MARCA',
        ], $rules);

        $this->assertFalse($validator->fails());
    }
}

