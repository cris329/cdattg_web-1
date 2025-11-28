<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateFichaCaracterizacionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateFichaCaracterizacionRequestTest extends TestCase
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
    public function valida_fecha_fin_despues_de_fecha_inicio(): void
    {
        $rules = (new UpdateFichaCaracterizacionRequest)->rules();

        $validator = Validator::make([
            'fecha_inicio' => now()->addMonth()->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'),
        ], $rules);

        $this->assertTrue($validator->fails());
    }
}
