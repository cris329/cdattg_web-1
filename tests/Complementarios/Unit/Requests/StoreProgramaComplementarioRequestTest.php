<?php

namespace Tests\Complementarios\Unit\Requests;

use App\Http\Requests\Complementarios\StoreProgramaComplementarioRequest;
use App\Models\Ambiente;
use App\Models\JornadaFormacion;
use App\Models\ParametroTema;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Complementarios\Concerns\SeedsComplementariosDatabase;

class StoreProgramaComplementarioRequestTest extends TestCase
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
        // Obtener IDs válidos de las tablas relacionadas
        $modalidad = ParametroTema::where('tema_id', 5)
            ->whereIn('parametro_id', [18, 19, 20])
            ->first();
        
        $jornada = JornadaFormacion::first();
        $ambiente = Ambiente::first();

        $datos = [
            'codigo' => 'COMP001',
            'nombre' => 'Programa Complementario Test',
            'justificacion' => 'Esta es una justificación de prueba para el programa complementario.',
            'requisitos_ingreso' => 'Requisitos de ingreso para el programa complementario.',
            'duracion' => 40,
            'cupos' => 20,
            'estado' => 1,
            'modalidad_id' => $modalidad->id ?? 1,
            'jornada_id' => $jornada->id ?? 1,
            'ambiente_id' => $ambiente->id ?? 1,
        ];

        $request = new StoreProgramaComplementarioRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->passes());
    }

    #[Test]
    public function rechaza_codigo_faltante(): void
    {
        $datos = [
            'nombre' => 'Programa Test',
        ];

        $request = new StoreProgramaComplementarioRequest;
        $validator = Validator::make($datos, $request->rules());

        $this->assertTrue($validator->fails());
    }
}

