<?php

namespace Database\Factories\Inventario;

use App\Models\Inventario\Proveedor;
use Database\Factories\Concerns\HasUserId;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventario\Proveedor>
 */
class ProveedorFactory extends Factory
{
    use HasUserId;

    protected $model = Proveedor::class;

    public function definition(): array
    {
        static $usedNits = [];
        
        // Obtener ubicación válida de los seeders o usar null
        // Asegurar que el municipio pertenezca al departamento
        // Ambos campos son nullable según las migraciones
        $departamentoId = null;
        $municipioId = null;
        
        try {
            // Primero obtener un municipio y luego su departamento para garantizar la relación
            $municipio = \App\Models\Municipio::query()->inRandomOrder()->first();
            if ($municipio && $municipio->departamento_id) {
                $municipioId = $municipio->id;
                $departamentoId = $municipio->departamento_id;
            } else {
                // Si no hay municipios, intentar solo departamento
                $departamento = \App\Models\Departamento::query()->inRandomOrder()->first();
                if ($departamento) {
                    $departamentoId = $departamento->id;
                    // municipio_id queda null
                }
            }
        } catch (\Exception $e) {
            // Ignorar error de consulta, usar null (campos son nullable)
        }

        $empresas = ['TECNOLOGÍA', 'SISTEMAS', 'SUMINISTROS', 'EQUIPOS', 'COMERCIAL', 'DISTRIBUCIONES', 'IMPORTADORA', 'SOLUCIONES'];
        $sufijos = ['LTDA', 'S.A.S', 'S.A', 'E.U'];
        $proveedor = strtoupper($empresas[array_rand($empresas)] . ' ' . $empresas[array_rand($empresas)] . ' ' . $sufijos[array_rand($sufijos)]);
        
        // Generar NIT único
        do {
            $nit = rand(100000000, 999999999) . '-' . rand(0, 9);
        } while (in_array($nit, $usedNits));
        $usedNits[] = $nit;

        // Obtener estado_id válido o usar null (es nullable)
        $estadoId = null;
        try {
            $estadoId = \App\Models\ParametroTema::query()
                ->whereHas('tema', function ($query) {
                    $query->where('name', 'ESTADOS');
                })
                ->whereHas('parametro', function ($query) {
                    $query->where('name', 'ACTIVO');
                })
                ->value('id');
        } catch (\Exception $e) {
            // Ignorar error, usar null (campo es nullable)
        }

        // Obtener persona_id con rol PROVEEDOR o usar null (es nullable)
        $personaId = null;
        try {
            $persona = \App\Models\Persona::query()
                ->where('status', 1)
                ->whereHas('user', function ($query) {
                    $query->whereHas('roles', function ($roleQuery) {
                        $roleQuery->where('name', 'PROVEEDOR');
                    });
                })
                ->inRandomOrder()
                ->first();
            
            if ($persona) {
                $personaId = $persona->id;
            }
        } catch (\Exception $e) {
            // Ignorar error, usar null (campo es nullable)
        }

        return [
            'name' => $proveedor,
            'nit' => $nit,
            'email' => strtolower('contacto' . rand(100, 999) . '@' . str_replace(' ', '', strtolower($empresas[array_rand($empresas)])) . '.com'),
            'telefono' => '60' . rand(10000000, 99999999),
            'direccion' => 'Calle ' . rand(1, 100) . ' #' . rand(1, 50) . '-' . rand(1, 99),
            'departamento_id' => $departamentoId,
            'municipio_id' => $municipioId,
            'persona_id' => $personaId,
            'estado_id' => $estadoId,
            'user_create_id' => $this->getUserId(),
            'user_update_id' => $this->getUserId(),
        ];
    }
}


