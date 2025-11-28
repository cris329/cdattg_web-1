<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateRegionalRequest;
use App\Models\Regional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateRegionalRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
        ]);
    }

    #[Test]
    public function valida_datos_validos(): void
    {
        $regional = Regional::factory()->create();

        $datos = [
            'nombre' => 'Regional Actualizada',
            'status' => true,
        ];

        $request = new UpdateRegionalRequest;
        $request->setRouteResolver(function () use ($regional) {
            return new class($regional) {
                public function parameter($name) {
                    return $name === 'regional' ? $this->regional : null;
                }
                public function __construct($regional) {
                    $this->regional = $regional;
                }
            };
        });

        $rules = $request->rules();
        $validator = Validator::make($datos, $rules);

        $this->assertTrue($validator->passes());
    }
}

