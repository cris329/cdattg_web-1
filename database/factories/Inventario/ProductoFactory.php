<?php

namespace Database\Factories\Inventario;

use App\Exceptions\ProductoFactoryException;
use App\Models\Ambiente;
use App\Models\Inventario\ContratoConvenio;
use App\Models\Inventario\Producto;
use App\Models\Inventario\Proveedor;
use Database\Factories\Concerns\HasParametroTema;
use Database\Factories\Concerns\HasUserId;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\Producto>
 */
class ProductoFactory extends Factory
{
    use HasUserId, HasParametroTema;

    protected $model = Producto::class;

    /**
     * Sobrescribe el método para usar ProductoFactoryException
     */
    protected function getParametroTemaExceptionClass(): string
    {
        return ProductoFactoryException::class;
    }

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
            'name' => $producto,
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

}


