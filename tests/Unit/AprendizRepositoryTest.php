<?php

namespace Tests\Unit;

use App\Models\Aprendiz;
use App\Models\FichaCaracterizacion;
use App\Repositories\AprendizRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AprendizRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected AprendizRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\RedConocimientoSeeder::class,
        ]);

        $this->repository = app(AprendizRepository::class);
    }

    #[Test]
    public function puede_obtener_aprendices_con_filtros(): void
    {
        Aprendiz::factory()->count(10)->create();

        $filtros = ['per_page' => 5];
        $resultado = $this->repository->obtenerAprendicesConFiltros($filtros);

        $this->assertCount(5, $resultado->items());
    }

    #[Test]
    public function puede_buscar_aprendices_por_termino(): void
    {
        $persona = \App\Models\Persona::factory()->create([
            'primer_nombre' => 'Juan',
        ]);
        Aprendiz::factory()->create(['persona_id' => $persona->id]);

        $filtros = ['search' => 'Juan'];
        $resultado = $this->repository->obtenerAprendicesConFiltros($filtros);

        $this->assertGreaterThan(0, $resultado->total());
    }

    #[Test]
    public function puede_obtener_aprendices_por_ficha(): void
    {
        $ficha = FichaCaracterizacion::factory()->create();
        Aprendiz::factory()->count(3)->create([
            'ficha_caracterizacion_id' => $ficha->id,
            'estado' => true,
        ]);

        $resultado = $this->repository->obtenerPorFicha($ficha->id);

        $this->assertCount(3, $resultado);
    }

    #[Test]
    public function puede_encontrar_aprendiz_con_relaciones(): void
    {
        $aprendiz = Aprendiz::factory()->create();

        $resultado = $this->repository->encontrarConRelaciones($aprendiz->id);

        $this->assertNotNull($resultado);
        $this->assertTrue($resultado->relationLoaded('persona'));
        $this->assertTrue($resultado->relationLoaded('fichaCaracterizacion'));
    }
}
