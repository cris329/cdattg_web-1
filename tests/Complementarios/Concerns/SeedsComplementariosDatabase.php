<?php

declare(strict_types=1);

namespace Tests\Complementarios\Concerns;

use App\Models\Parametro;
use Illuminate\Support\Facades\Schema;

/**
 * Trait para optimizar el seeding de la base de datos en tests de Complementarios.
 * Solo ejecuta los seeders si los datos base no existen, mejorando significativamente
 * el rendimiento de los tests.
 * IMPORTANTE: Este trait funciona con RefreshDatabase. Verifica si las tablas existen
 * y tienen datos antes de ejecutar los seeders, evitando ejecuciones innecesarias.
 */
trait SeedsComplementariosDatabase
{
    /**
     * Ejecuta los seeders necesarios solo si los datos base no existen.
     * Esto evita re-ejecutar seeders costosos en cada test.
     */
    protected function seedComplementariosDatabaseIfNeeded(): void
    {
        // Verificar si la tabla existe y si ya hay datos
        // Esto es importante porque RefreshDatabase recrea la BD pero los seeders
        // pueden no haberse ejecutado aún en este test específico
        try {
            if (Schema::hasTable('parametros') && Parametro::count() > 0) {
                return;
            }
        } catch (\Exception $e) {
            // Si hay error al verificar, continuar y ejecutar seeders
        }

        $this->seed([
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\ParametroSeeder::class,
            \Database\Seeders\TemaSeeder::class,
            \Database\Seeders\PaisSeeder::class,
            \Database\Seeders\DepartamentoSeeder::class,
            \Database\Seeders\MunicipioSeeder::class,
            \Database\Seeders\PersonaSeeder::class,
            \Database\Seeders\UsersSeeder::class,
            \Database\Seeders\RegionalSeeder::class,
            \Database\Seeders\CentroFormacionSeeder::class,
            \Database\Seeders\SedeSeeder::class,
            \Database\Seeders\BloqueSeeder::class,
            \Database\Seeders\PisoSeeder::class,
            \Database\Seeders\AmbienteSeeder::class,
            \Database\Seeders\JornadaFormacionSeeder::class,
        ]);
    }
}

