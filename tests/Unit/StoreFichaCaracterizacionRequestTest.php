<?php

namespace Tests\Unit;

use App\Http\Requests\StoreFichaCaracterizacionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreFichaCaracterizacionRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);
    }

    #[Test]
    public function valida_datos_requeridos(): void
    {
        $rules = (new StoreFichaCaracterizacionRequest)->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('ficha', $validator->errors()->toArray());
        $this->assertArrayHasKey('programa_formacion_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('fecha_inicio', $validator->errors()->toArray());
        $this->assertArrayHasKey('fecha_fin', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_fecha_fin_despues_de_fecha_inicio(): void
    {
        $programa = \App\Models\ProgramaFormacion::factory()->create();

        $rules = (new StoreFichaCaracterizacionRequest)->rules();

        $validator = Validator::make([
            'ficha' => '123456',
            'programa_formacion_id' => $programa->id,
            'fecha_inicio' => now()->addMonth()->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'), // Fecha anterior a inicio
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('fecha_fin', $validator->errors()->toArray());
    }
}
