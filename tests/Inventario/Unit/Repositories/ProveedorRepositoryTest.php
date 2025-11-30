<?php

namespace Tests\Inventario\Unit\Repositories;

use Tests\TestCase;
use App\Inventario\Repositories\Proveedor\ProveedorRepository;
use App\Models\Inventario\Proveedor;
use App\Models\Inventario\ContratoConvenio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ProveedorRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private const PROVEEDOR_TEST = 'PROVEEDOR TEST';
    protected ProveedorRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProveedorRepository();
        
        // Ejecutar seeders necesarios
        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\PersonaSeeder::class,
            \Database\Seeders\UsersSeeder::class,
        ]);
    }

    #[Test]
    public function puede_obtener_todos_los_proveedores()
    {
        Proveedor::factory()->count(3)->create();

        $resultado = $this->repository->obtenerTodos();

        $this->assertCount(3, $resultado);
    }

    #[Test]
    public function puede_obtener_proveedores_con_filtros()
    {
        Proveedor::factory()->create(['proveedor' => self::PROVEEDOR_TEST]);
        Proveedor::factory()->create(['proveedor' => 'OTRO PROVEEDOR']);

        $resultado = $this->repository->obtenerConFiltros();

        $this->assertGreaterThanOrEqual(2, $resultado->total());
    }

    #[Test]
    public function puede_filtrar_proveedores_por_busqueda()
    {
        Proveedor::factory()->create(['proveedor' => 'TECNOLOGIA S.A.S']);
        Proveedor::factory()->create(['proveedor' => 'SUMINISTROS LTDA']);

        $resultado = $this->repository->obtenerConFiltros(['search' => 'TECNOLOGIA']);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }

    #[Test]
    public function puede_filtrar_proveedores_por_nit()
    {
        Proveedor::factory()->create(['nit' => '123456789-0']);
        Proveedor::factory()->create(['nit' => '987654321-1']);

        $resultado = $this->repository->obtenerConFiltros(['search' => '123456789']);

        $this->assertGreaterThanOrEqual(1, $resultado->total());
    }

    #[Test]
    public function puede_encontrar_proveedor_con_relaciones()
    {
        $proveedor = Proveedor::factory()->create();

        $resultado = $this->repository->encontrarConRelaciones($proveedor->id);

        $this->assertNotNull($resultado);
        $this->assertTrue($resultado->relationLoaded('contratosConvenios'));
        $this->assertTrue($resultado->relationLoaded('estado'));
    }

    #[Test]
    public function puede_crear_proveedor()
    {
        $estado = \App\Models\ParametroTema::query()->inRandomOrder()->first();
        
        // Si no hay estado disponible, crear uno básico
        if (!$estado) {
            $tema = \App\Models\Tema::firstOrCreate(
                ['name' => 'ESTADOS'],
                ['status' => 1, 'user_create_id' => null, 'user_edit_id' => null]
            );
            $parametro = \App\Models\Parametro::firstOrCreate(
                ['name' => 'ACTIVO'],
                ['status' => 1, 'user_create_id' => null, 'user_edit_id' => null]
            );
            $tema->parametros()->syncWithoutDetaching([
                $parametro->id => ['status' => 1]
            ]);
            $estado = \App\Models\ParametroTema::query()
                ->where('tema_id', $tema->id)
                ->where('parametro_id', $parametro->id)
                ->first();
        }
        
        $datos = [
            'proveedor' => self::PROVEEDOR_TEST,
            'nit' => '123456789-0',
            'email' => 'test@example.com',
            'telefono' => '6012345678',
            'estado_id' => $estado->id,
            'user_create_id' => 1,
            'user_update_id' => 1,
        ];

        $resultado = $this->repository->crear($datos);

        $this->assertInstanceOf(Proveedor::class, $resultado);
        $this->assertEquals(self::PROVEEDOR_TEST, $resultado->proveedor);
    }

    #[Test]
    public function puede_actualizar_proveedor()
    {
        $proveedor = Proveedor::factory()->create(['proveedor' => 'ORIGINAL']);

        $resultado = $this->repository->actualizar($proveedor->id, ['proveedor' => 'ACTUALIZADO']);

        $this->assertTrue($resultado);
        $this->assertEquals('ACTUALIZADO', Proveedor::find($proveedor->id)->proveedor);
    }

    #[Test]
    public function puede_eliminar_proveedor()
    {
        $proveedor = Proveedor::factory()->create();

        $resultado = $this->repository->eliminar($proveedor->id);

        $this->assertTrue($resultado);
        $this->assertNull(Proveedor::find($proveedor->id));
    }

    #[Test]
    public function puede_verificar_si_proveedor_tiene_contratos()
    {
        $proveedor = Proveedor::factory()->create();
        ContratoConvenio::factory()->create(['proveedor_id' => $proveedor->id]);

        $resultado = $this->repository->tieneContratos($proveedor->id);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function retorna_false_si_proveedor_no_tiene_contratos()
    {
        $proveedor = Proveedor::factory()->create();

        $resultado = $this->repository->tieneContratos($proveedor->id);

        $this->assertFalse($resultado);
    }

    #[Test]
    public function puede_verificar_si_proveedor_tiene_productos()
    {
        $proveedor = Proveedor::factory()->create();
        \App\Models\Inventario\Producto::factory()->create(['proveedor_id' => $proveedor->id]);

        $resultado = $this->repository->tieneProductos($proveedor->id);

        $this->assertTrue($resultado);
    }

    #[Test]
    public function retorna_false_si_proveedor_no_tiene_productos()
    {
        $proveedor = Proveedor::factory()->create();

        $resultado = $this->repository->tieneProductos($proveedor->id);

        $this->assertFalse($resultado);
    }
}

