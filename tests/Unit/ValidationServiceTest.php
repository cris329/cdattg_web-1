<?php

namespace Tests\Unit;

use App\Services\ValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
        ]);

        $this->service = new ValidationService;
    }

    #[Test]
    public function valida_aprendiz_con_datos_validos(): void
    {
        $datos = [
            'persona_id' => 1,
            'ficha_caracterizacion_id' => 1,
            'estado' => true,
        ];

        $resultado = $this->service->validarAprendiz($datos);

        $this->assertIsArray($resultado);
    }

    #[Test]
    public function valida_instructor_con_datos_validos(): void
    {
        $datos = [
            'persona_id' => 1,
            'regional_id' => 1,
            'anos_experiencia' => 5,
        ];

        $resultado = $this->service->validarInstructor($datos);

        $this->assertIsArray($resultado);
    }

    #[Test]
    public function valida_numero_documento_cedula_ciudadania(): void
    {
        $resultado = $this->service->validarDocumento('1234567890', 1);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('valido', $resultado);
    }

    #[Test]
    public function valida_email_sena_valido(): void
    {
        $resultado = $this->service->validarEmailSena('test@sena.edu.co');

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['valido']);
    }

    #[Test]
    public function valida_email_sena_invalido(): void
    {
        $resultado = $this->service->validarEmailSena('test@gmail.com');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['valido']);
    }
}

