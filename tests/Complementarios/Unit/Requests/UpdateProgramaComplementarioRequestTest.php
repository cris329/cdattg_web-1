<?php

namespace Tests\Complementarios\Unit\Requests;

use App\Http\Requests\Complementarios\UpdateProgramaComplementarioRequest;
use App\Models\Ambiente;
use App\Models\ComplementarioOfertado;
use App\Models\JornadaFormacion;
use App\Models\ParametroTema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateProgramaComplementarioRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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
            \Database\Seeders\CentroFormacionSeeder::class,
            \Database\Seeders\SedeSeeder::class,
            \Database\Seeders\BloqueSeeder::class,
            \Database\Seeders\PisoSeeder::class,
            \Database\Seeders\AmbienteSeeder::class,
            \Database\Seeders\JornadaFormacionSeeder::class,
        ]);
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

