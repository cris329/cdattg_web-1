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

    private const MARCA_TEST = 'MARCA TEST';

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateDatabases();
    }

    private function obtenerRules(): array
    {
        $request = new MarcaCategoriaRequest();
        return $request->rules();
    }

    private function obtenerRulesParaUpdate(Parametro $parametro): array
    {
        $request = new MarcaCategoriaRequest();
        $request->setMethod('PUT');
        $request->setRouteResolver(function () use ($parametro) {
            return $this->crearRouteResolver($parametro);
        });
        return $request->rules();
    }

    private function crearRouteResolver(Parametro $parametro): object
    {
        return new class($parametro) {
            private $parametro;
            
            public function __construct($parametro) {
                $this->parametro = $parametro;
            }
            
            public function parameter(string $name): ?Parametro
            {
                if ($name === 'categoria') {
                    return $this->parametro;
                }
                return null;
            }
        };
    }

    private function validarYVerificarError(array $data, array $rules, string $campoEsperado): void
    {
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey($campoEsperado, $validator->errors()->toArray());
    }

    #[Test]
    public function valida_name_requerido_en_store(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError([], $rules, 'name');
    }

    #[Test]
    public function valida_name_debe_ser_string(): void
    {
        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            ['name' => 123],
            $rules,
            'name'
        );
    }

    #[Test]
    public function valida_unicidad_de_name_en_store(): void
    {
        Parametro::factory()->create(['name' => self::MARCA_TEST]);

        $rules = $this->obtenerRules();

        $this->validarYVerificarError(
            ['name' => self::MARCA_TEST],
            $rules,
            'name'
        );
    }

    #[Test]
    public function valida_unicidad_de_name_en_update(): void
    {
        Parametro::factory()->create(['name' => 'MARCA 1']);
        $parametro2 = Parametro::factory()->create(['name' => 'MARCA 2']);

        $rules = $this->obtenerRulesParaUpdate($parametro2);

        $this->validarYVerificarError(
            ['name' => 'MARCA 1'],
            $rules,
            'name'
        );
    }

    #[Test]
    public function permite_mismo_name_en_update(): void
    {
        $parametro = Parametro::factory()->create(['name' => self::MARCA_TEST]);

        $rules = $this->obtenerRulesParaUpdate($parametro);

        $validator = Validator::make(['name' => self::MARCA_TEST], $rules);

        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function acepta_datos_validos_para_store(): void
    {
        $rules = $this->obtenerRules();

        $validator = Validator::make(['name' => 'NUEVA MARCA'], $rules);

        $this->assertFalse($validator->fails());
    }
}

