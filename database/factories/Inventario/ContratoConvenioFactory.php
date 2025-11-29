<?php

namespace Database\Factories\Inventario;

use App\Models\Inventario\ContratoConvenio;
use App\Models\Inventario\Proveedor;
use Database\Factories\Concerns\HasUserId;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\ContratoConvenio>
 */
class ContratoConvenioFactory extends Factory
{
    use HasUserId;

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

    /**
     * Obtiene un parametro_tema aleatorio o crea uno básico si no existe ninguno
     */
    private function obtenerParametroTemaAleatorio(): int
    {
        try {
            $parametroTemaId = \App\Models\ParametroTema::query()->inRandomOrder()->value('id');
            if ($parametroTemaId) {
                return $parametroTemaId;
            }
        } catch (\Exception $e) {
            // Ignorar error de consulta
        }

        // Si no hay parametros_temas, intentar crear uno básico
        try {
            $tema = \App\Models\Tema::query()->inRandomOrder()->first();
            $parametro = \App\Models\Parametro::query()->inRandomOrder()->first();
            
            // Si no hay tema, crear uno básico (user_create_id y user_edit_id son nullable)
            if (!$tema) {
                $tema = \App\Models\Tema::query()->create([
                    'name' => 'TEMA FACTORY ' . uniqid(),
                    'status' => 1,
                    'user_create_id' => null,
                    'user_edit_id' => null,
                ]);
            }
            
            // Si no hay parametro, crear uno básico (user_create_id y user_edit_id son nullable)
            if (!$parametro) {
                $parametro = \App\Models\Parametro::query()->create([
                    'name' => 'PARAMETRO FACTORY ' . uniqid(),
                    'status' => 1,
                    'user_create_id' => null,
                    'user_edit_id' => null,
                ]);
            }
            
            // Crear el parametro_tema
            $tema->parametros()->syncWithoutDetaching([
                $parametro->id => ['status' => 1]
            ]);
            
            $parametroTema = \App\Models\ParametroTema::query()
                ->where('tema_id', $tema->id)
                ->where('parametro_id', $parametro->id)
                ->orderBy('id', 'desc')
                ->first();
            
            if ($parametroTema) {
                return $parametroTema->id;
            }
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'No se encontró ningún parametro_tema y no se pudo crear uno. ' .
                'Error: ' . $e->getMessage() . '. ' .
                'Ejecuta los seeders necesarios (TemaSeeder, ParametroSeeder).',
                0,
                $e
            );
        }

        throw new \RuntimeException(
            'No se encontró ningún parametro_tema y no se pudo crear uno. ' .
            'Ejecuta los seeders necesarios (TemaSeeder, ParametroSeeder).'
        );
    }
}


