<?php

namespace Database\Factories\Inventario;

use App\Exceptions\InventarioFactoryException;
use App\Models\Inventario\ContratoConvenio;
use App\Models\Inventario\Proveedor;
use Database\Factories\Concerns\HasParametroTema;
use Database\Factories\Concerns\HasUserId;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\ContratoConvenio>
 */
class ContratoConvenioFactory extends Factory
{
    use HasUserId, HasParametroTema;

    protected $model = ContratoConvenio::class;

    public function definition(): array
    {
        static $usedNames = [];
        static $counter = 1;
        
        $mesesAtras = rand(0, 3);
        $fechaInicio = Carbon::now()->subMonths($mesesAtras);
        $fechaFin = (clone $fechaInicio)->addYear();

        $palabras = ['CONTRATO', 'CONVENIO', 'SUMINISTRO', 'SERVICIOS', 'EQUIPOS', 'ADQUISICIÓN', 'COMPRA', 'MANTENIMIENTO'];
        
        // Generar nombre único
        do {
            $name = strtoupper(
                $palabras[array_rand($palabras)] . ' ' .
                $palabras[array_rand($palabras)] . ' ' .
                rand(2024, 2025) . '-' . 
                str_pad($counter, 3, '0', STR_PAD_LEFT)
            );
            $counter++;
        } while (in_array($name, $usedNames));
        $usedNames[] = $name;
        
        $letras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codigo = strtoupper(
            $letras[rand(0, 25)] . $letras[rand(0, 25)] . '-' .
            rand(10, 99) . $letras[rand(0, 25)] . $letras[rand(0, 25)] . '-' .
            rand(1000, 9999)
        );

        // Obtener o crear proveedor - campo requerido
        $proveedorId = null;
        try {
            $proveedorId = Proveedor::query()->inRandomOrder()->value('id');
        } catch (\Exception $e) {
            // Ignorar error de consulta
        }

        if (!$proveedorId) {
            $proveedorId = Proveedor::factory()->create()->id;
        }

        // Obtener estado_id de parametros_temas - campo NOT NULL
        $estadoId = $this->obtenerParametroTemaAleatorio();

        return [
            'name' => $name,
            'codigo' => $codigo,
            'proveedor_id' => $proveedorId,
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
            'estado_id' => $estadoId,
            'user_create_id' => $this->getUserId(),
            'user_update_id' => $this->getUserId(),
        ];
    }

}


