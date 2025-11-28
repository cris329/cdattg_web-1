<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateCompetenciaRequest;
use App\Models\Competencia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateCompetenciaRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);
    }

    #[Test]
    public function valida_datos_validos(): void
    {
        $competencia = Competencia::factory()->create();

        $datos = [
            'descripcion' => 'Descripción actualizada',
            'codigo' => 'COMP002',
            'nombre' => 'Competencia Actualizada',
            'duracion' => 150,
            'status' => true,
        ];

        $request = new UpdateCompetenciaRequest;
        $request->setRouteResolver(function () use ($competencia) {
            return new class($competencia) {
                public function parameter($name) {
                    return $name === 'competencia' ? $this->competencia : null;
                }
                public function __construct($competencia) {
                    $this->competencia = $competencia;
                }
            };
        });

        $rules = $request->rules();
        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }
}

