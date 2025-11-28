<?php

namespace Tests\Unit;

use App\Http\Requests\StorePersonaRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StorePersonaRequestTest extends TestCase
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
            \Database\Seeders\MunicipioSeeder::class,
        ]);
    }

    #[Test]
    public function valida_datos_requeridos(): void
    {
        $rules = (new StorePersonaRequest)->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tipo_documento', $validator->errors()->toArray());
        $this->assertArrayHasKey('numero_documento', $validator->errors()->toArray());
        $this->assertArrayHasKey('primer_nombre', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_numero_documento_unico(): void
    {
        $persona = \App\Models\Persona::factory()->create([
            'numero_documento' => '1234567890',
        ]);

        $rules = (new StorePersonaRequest)->rules();

        $validator = Validator::make([
            'numero_documento' => '1234567890',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('numero_documento', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_email_unico(): void
    {
        $persona = \App\Models\Persona::factory()->create([
            'email' => 'test@example.com',
        ]);

        $rules = (new StorePersonaRequest)->rules();

        $validator = Validator::make([
            'email' => 'test@example.com',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }
}
