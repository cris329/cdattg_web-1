<?php

namespace Database\Factories\Inventario;

use App\Exceptions\InventarioFactoryException;
use App\Models\Inventario\Aprobacion;
use App\Models\Inventario\DetalleOrden;
use Database\Factories\Concerns\HasParametroTema;
use Database\Factories\Concerns\HasUserId;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\Aprobacion>
 */
class AprobacionFactory extends Factory
{
    use HasUserId, HasParametroTema;

    protected $model = Aprobacion::class;

    public function definition(): array
    {
        // Obtener o crear detalle orden
        $detalleOrdenId = null;
        try {
            $detalleOrdenId = DetalleOrden::query()->inRandomOrder()->value('id');
        } catch (\Exception $e) {
            // Ignorar error de consulta
        }

        if (!$detalleOrdenId) {
            try {
                $detalleOrdenId = DetalleOrden::factory()->create()->id;
            } catch (\Throwable $e) {
                throw new InventarioFactoryException(
                    'No se pudo crear un DetalleOrden para la Aprobacion. Error: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        // Obtener estado_aprobacion_id de parametros_temas - campo NOT NULL
        $estadoAprobacionId = $this->obtenerParametroTemaAleatorio();

        return [
            'detalle_orden_id' => $detalleOrdenId,
            'estado_aprobacion_id' => $estadoAprobacionId,
            'user_create_id' => $this->getUserId(),
            'user_update_id' => $this->getUserId(),
        ];
    }

}


