<?php

namespace Tests\Complementarios\Unit\Services\Sofia;

use Tests\TestCase;
use App\Services\Complementarios\Sofia\SofiaStateMapper;
use PHPUnit\Framework\Attributes\Test;

class SofiaStateMapperTest extends TestCase
{
    private SofiaStateMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new SofiaStateMapper();
    }

    #[Test]
    public function mapea_resultado_ya_existe_a_estado_registrado(): void
    {
        $estado = $this->mapper->mapToState('YA_EXISTE');

        $this->assertEquals(1, $estado);
    }

    #[Test]
    public function mapea_resultado_no_registrado_a_estado_no_registrado(): void
    {
        $estado = $this->mapper->mapToState('NO_REGISTRADO');

        $this->assertEquals(0, $estado);
    }

    #[Test]
    public function mapea_resultado_requiere_cambio_a_estado_requiere_cambio(): void
    {
        $estado = $this->mapper->mapToState('REQUIERE_CAMBIO');

        $this->assertEquals(2, $estado);
    }

    #[Test]
    public function mapea_resultado_error_a_estado_no_registrado(): void
    {
        $estado = $this->mapper->mapToState('ERROR');

        $this->assertEquals(0, $estado);
    }

    #[Test]
    public function mapea_resultado_desconocido_a_estado_no_registrado(): void
    {
        $estado = $this->mapper->mapToState('DESCONOCIDO');

        $this->assertEquals(0, $estado);
    }

    #[Test]
    public function mapea_patron_requiere_cambio_a_estado_requiere_cambio(): void
    {
        $estados = [
            $this->mapper->mapToState('requiere_cambio'),
            $this->mapper->mapToState('actualizar tu documento'),
            $this->mapper->mapToState('cambiar tu documento'),
            $this->mapper->mapToState('tarjeta de identidad'),
        ];

        foreach ($estados as $estado) {
            $this->assertEquals(2, $estado);
        }
    }

    #[Test]
    public function mapea_patron_registrado_a_estado_registrado(): void
    {
        $estados = [
            $this->mapper->mapToState('ya existe'),
            $this->mapper->mapToState('ya cuentas con un registro'),
            $this->mapper->mapToState('cuenta registrada'),
        ];

        foreach ($estados as $estado) {
            $this->assertEquals(1, $estado);
        }
    }

    #[Test]
    public function mapea_patron_no_registrado_a_estado_no_registrado(): void
    {
        $estados = [
            $this->mapper->mapToState('no_registrado'),
            $this->mapper->mapToState('desconocido'),
            $this->mapper->mapToState(''),
        ];

        foreach ($estados as $estado) {
            $this->assertEquals(0, $estado);
        }
    }

    #[Test]
    public function mapea_resultado_con_error_en_texto_a_estado_no_registrado(): void
    {
        $estado = $this->mapper->mapToState('Error al procesar');

        $this->assertEquals(0, $estado);
    }

    #[Test]
    public function obtiene_label_para_estado_no_registrado(): void
    {
        $label = $this->mapper->getStateLabel(0);

        $this->assertEquals('No registrado', $label);
    }

    #[Test]
    public function obtiene_label_para_estado_registrado(): void
    {
        $label = $this->mapper->getStateLabel(1);

        $this->assertEquals('Registrado', $label);
    }

    #[Test]
    public function obtiene_label_para_estado_requiere_cambio(): void
    {
        $label = $this->mapper->getStateLabel(2);

        $this->assertEquals('Requiere cambio', $label);
    }

    #[Test]
    public function obtiene_label_desconocido_para_estado_invalido(): void
    {
        $label = $this->mapper->getStateLabel(999);

        $this->assertEquals('Desconocido', $label);
    }

    #[Test]
    public function mapea_resultado_insensible_a_mayusculas(): void
    {
        $estado1 = $this->mapper->mapToState('YA_EXISTE');
        $estado2 = $this->mapper->mapToState('ya_existe');
        $estado3 = $this->mapper->mapToState('Ya_Existe');

        $this->assertEquals(1, $estado1);
        $this->assertEquals(1, $estado2);
        $this->assertEquals(1, $estado3);
    }
}

