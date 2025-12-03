<?php

namespace Database\Factories\Inventario;

use App\Exceptions\InventarioFactoryException;
use App\Models\Inventario\Orden;
use Database\Factories\Concerns\HasParametroTema;
use Database\Factories\Concerns\HasUserId;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\Orden>
 */
class OrdenFactory extends Factory
{
    use HasUserId, HasParametroTema;

    protected $model = Orden::class;

    public function definition(): array
    {
        // Obtener tipo_orden_id de parametros_temas - campo NOT NULL
        $tipoOrdenId = $this->obtenerParametroTemaAleatorio();
        
        $diasAdelante = random_int(7, 120);
        // fecha_devolucion es nullable según la migración
        $fechaDevolucion = random_int(0, 1) === 1
            ? date('Y-m-d', strtotime("+{$diasAdelante} days"))
            : null;

        $descripciones = [
            'ORDEN DE COMPRA EQUIPOS TECNOLÓGICOS',
            'ORDEN DE SUMINISTRO MATERIALES',
            'ORDEN DE SERVICIO MANTENIMIENTO',
            'ORDEN DE COMPRA HERRAMIENTAS',
        ];

        return [
            'descripcion_orden' => strtoupper($descripciones[random_int(0, count($descripciones) - 1)]),
            'tipo_orden_id' => $tipoOrdenId,
            'fecha_devolucion' => $fechaDevolucion,
            'user_create_id' => $this->getUserId(),
            'user_update_id' => $this->getUserId(),
        ];
    }

}


