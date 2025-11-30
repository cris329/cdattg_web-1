<?php

namespace Database\Factories\Inventario;

use App\Models\Ambiente;
use App\Models\Inventario\ContratoConvenio;
use App\Models\Inventario\Producto;
use App\Models\Inventario\Proveedor;
use Database\Factories\Concerns\HasUserId;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\Producto>
 */
class ProductoFactory extends Factory
{
    use HasUserId;

    protected $model = Producto::class;

    public function definition(): array
    {
        // Obtener o crear ambiente
        $ambienteId = null;
        try {
            $ambienteId = Ambiente::query()->inRandomOrder()->value('id');
        } catch (\Exception $e) {
            // Ignorar error de consulta
        }

        if (!$ambienteId) {
            try {
                $ambienteId = Ambiente::factory()->create()->id;
            } catch (\Exception $e) {
                // Si falla, usar null si es nullable, o lanzar excepción
                $ambienteId = null;
            }
        }

        // Obtener o crear contrato convenio
        $contratoConvenioId = null;
        try {
            $contratoConvenioId = ContratoConvenio::query()->inRandomOrder()->value('id');
        } catch (\Exception $e) {
            // Ignorar error de consulta
        }

        if (!$contratoConvenioId) {
            try {
                $contratoConvenioId = ContratoConvenio::factory()->create()->id;
            } catch (\Exception $e) {
                // Si falla, usar null si es nullable
                $contratoConvenioId = null;
            }
        }

        // Obtener o crear proveedor
        $proveedorId = null;
        try {
            $proveedorId = Proveedor::query()->inRandomOrder()->value('id');
        } catch (\Exception $e) {
            // Ignorar error de consulta
        }

        if (!$proveedorId) {
            try {
                $proveedorId = Proveedor::factory()->create()->id;
            } catch (\Exception $e) {
                // Si falla, usar null si es nullable
                $proveedorId = null;
            }
        }

        // Obtener IDs de parametros_temas - campos NOT NULL
        // Intentar obtener cualquier parametro_tema existente
        $tipoProductoId = $this->obtenerParametroTemaAleatorio();
        $unidadMedidaId = $this->obtenerParametroTemaAleatorio();
        $estadoProductoId = $this->obtenerParametroTemaAleatorio();

        // Campos nullable
        $categoriaId = null;
        $marcaId = null;

        try {
            $categoriaId = \App\Models\Inventario\Categoria::query()->inRandomOrder()->value('id');
            $marcaId = \App\Models\Inventario\Marca::query()->inRandomOrder()->value('id');
        } catch (\Exception $e) {
            // Ignorar errores, campos son nullable
        }

        $productos = ['COMPUTADOR', 'MONITOR', 'TECLADO', 'MOUSE', 'CABLE', 'SWITCH', 'ROUTER', 'ESCRITORIO', 'SILLA'];
        $producto = strtoupper($productos[array_rand($productos)] . ' ' . $productos[array_rand($productos)]);
        
        $descripciones = [
            'Producto de alta calidad para uso en ambientes formativos',
            'Equipo tecnológico para la formación profesional',
            'Material didáctico para el desarrollo de competencias',
            'Herramienta especializada para uso institucional',
        ];

        return [
            'producto' => $producto,
            'tipo_producto_id' => $tipoProductoId,
            'descripcion' => $descripciones[array_rand($descripciones)],
            'peso' => round(rand(50, 250000) / 100, 2),
            'unidad_medida_id' => $unidadMedidaId,
            'cantidad' => rand(1, 80),
            'codigo_barras' => rand(1000000000000, 9999999999999),
            'estado_producto_id' => $estadoProductoId,
            'categoria_id' => $categoriaId,
            'marca_id' => $marcaId,
            'contrato_convenio_id' => $contratoConvenioId,
            'ambiente_id' => $ambienteId,
            'proveedor_id' => $proveedorId,
            'fecha_vencimiento' => date('Y-m-d', strtotime('+' . rand(90, 730) . ' days')),
            'imagen' => 'img/inventario/producto-default.png',
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
            // Si falla la creación, lanzar excepción con más detalles
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


