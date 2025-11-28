<?php

namespace Tests\Unit;

use App\Services\PersonaImportNormalizer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PersonaImportNormalizerTest extends TestCase
{
    #[Test]
    public function normaliza_texto_a_minusculas_sin_acentos(): void
    {
        $resultado = PersonaImportNormalizer::normalizarTexto('José María');

        $this->assertEquals('jose maria', $resultado);
    }

    #[Test]
    public function limpia_numero_documento(): void
    {
        $resultado = PersonaImportNormalizer::limpiarNumeroDocumento('123.456.789');

        $this->assertEquals('123456789', $resultado);
    }

    #[Test]
    public function normaliza_email_valido(): void
    {
        $resultado = PersonaImportNormalizer::normalizarEmail('TEST@EXAMPLE.COM');

        $this->assertEquals('test@example.com', $resultado);
    }

    #[Test]
    public function normaliza_telefono(): void
    {
        $resultado = PersonaImportNormalizer::normalizarTelefono('(57) 300-123-4567');

        $this->assertEquals('573001234567', $resultado);
    }

    #[Test]
    public function retorna_null_para_valores_nulos(): void
    {
        $this->assertNull(PersonaImportNormalizer::normalizarEmail(null));
        $this->assertNull(PersonaImportNormalizer::normalizarTelefono(null));
    }
}

