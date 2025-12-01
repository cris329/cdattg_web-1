<?php

namespace Tests\Complementarios\Unit\Requests;

use App\Http\Requests\Complementarios\UpdateProgramaComplementarioRequest;
use App\Models\Ambiente;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\JornadaFormacion;
use App\Models\ParametroTema;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Complementarios\Concerns\SeedsComplementariosDatabase;

class UpdateProgramaComplementarioRequestTest extends TestCase
{
    use DatabaseTransactions;
    use SeedsComplementariosDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedComplementariosDatabaseIfNeeded();
    }

    #[Test]
    public function valida_datos_validos(): void
    {
        $programa = ComplementarioOfertado::factory()->create();

        // Obtener IDs válidos de las tablas relacionadas
        $modalidad = ParametroTema::where('tema_id', 5)
            ->whereIn('parametro_id', [18, 19, 20])
            ->first();
        
        $jornada = JornadaFormacion::first();
        $ambiente = Ambiente::first();

        $datos = [
            'codigo' => 'COMP002',
            'nombre' => 'Programa Actualizado',
            'justificacion' => 'Justificación de prueba actualizada.',
            'requisitos_ingreso' => 'Requisitos de ingreso actualizados.',
            'duracion' => 50,
            'cupos' => 25,
            'estado' => 1,
            'modalidad_id' => $modalidad->id ?? 1,
            'jornada_id' => $jornada->id ?? 1,
            'ambiente_id' => $ambiente->id ?? 1,
        ];

        // Crear un Request con la ruta configurada
        $httpRequest = Request::create('/programas/' . $programa->id, 'PUT', $datos);
        $route = new \Illuminate\Routing\Route(['PUT'], '/programas/{programa}', []);
        $route->bind($httpRequest);
        $route->setParameter('programa', $programa);
        
        $httpRequest->setRouteResolver(function () use ($route) {
            return $route;
        });

        $request = UpdateProgramaComplementarioRequest::createFrom($httpRequest);
        $rules = $request->rules();
        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }
}

