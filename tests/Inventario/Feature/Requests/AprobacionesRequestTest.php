<?php

declare(strict_types=1);

namespace Tests\Inventario\Feature\Request;

use Tests\TestCase;
use App\Http\Requests\Inventario\AprobacionesRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;

class AprobacionesRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateDatabases();
    }

    #[Test]
    public function valida_motivo_rechazo_requerido(): void
    {
        $request = new AprobacionesRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('motivo_rechazo', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_motivo_rechazo_debe_ser_string(): void
    {
        $request = new AprobacionesRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'motivo_rechazo' => 123,
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('motivo_rechazo', $validator->errors()->toArray());
    }

    #[Test]
    public function valida_longitud_maxima_de_motivo_rechazo(): void
    {
        $request = new AprobacionesRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'motivo_rechazo' => str_repeat('a', 1001),
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('motivo_rechazo', $validator->errors()->toArray());
    }

    #[Test]
    public function acepta_motivo_rechazo_valido(): void
    {
        $request = new AprobacionesRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'motivo_rechazo' => 'Motivo de rechazo válido',
        ], $rules);

        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function acepta_motivo_rechazo_con_longitud_maxima(): void
    {
        $request = new AprobacionesRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'motivo_rechazo' => str_repeat('a', 1000),
        ], $rules);

        $this->assertFalse($validator->fails());
    }
}

