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
        if ($this->shouldSkipSeeding()) {
            return;
        }

        $this->executeSeedersWithRetry();
    }

    /**
     * Determina si se debe omitir el seeding basado en el entorno y datos existentes.
     */
    private function shouldSkipSeeding(): bool
    {
        if (app()->environment('production')) {
            return $this->shouldSkipSeedingInProduction();
        }

        return $this->shouldSkipSeedingInDevelopment();
    }

    /**
     * Verifica si se debe omitir el seeding en entorno de producción.
     */
    private function shouldSkipSeedingInProduction(): bool
    {
        try {
            return Schema::hasTable('parametros') && \App\Models\Parametro::count() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verifica si se debe omitir el seeding en entorno de desarrollo/testing.
     */
    private function shouldSkipSeedingInDevelopment(): bool
    {
        try {
            $temaGeneroExists = Schema::hasTable('temas') &&
                \App\Models\Tema::where('id', 3)->exists();

            $parametroTemaExists = Schema::hasTable('parametros_temas') &&
                \App\Models\ParametroTema::where('tema_id', 3)
                    ->whereIn('parametro_id', [9, 10, 11])
                    ->exists();

            return $temaGeneroExists && $parametroTemaExists;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Ejecuta los seeders con manejo de reintentos para deadlocks.
     */
    private function executeSeedersWithRetry(): void
    {
        $maxRetries = 3;
        $retryCount = 0;
        $seeded = false;

        while ($retryCount < $maxRetries && !$seeded) {
            try {
                $this->executeSeedersTransaction();
                $seeded = true;
            } catch (\Illuminate\Database\QueryException $e) {
                if ($this->isDeadlockException($e)) {
                    $retryCount = $this->handleDeadlockRetry($retryCount, $maxRetries);
                } else {
                    throw $e;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                break;
            }
        }
    }

    /**
     * Ejecuta los seeders dentro de una transacción.
     */
    private function executeSeedersTransaction(): void
    {
        \Illuminate\Support\Facades\DB::beginTransaction();

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

        \Illuminate\Support\Facades\DB::commit();
    }

    /**
     * Verifica si una excepción es un deadlock.
     */
    private function isDeadlockException(\Illuminate\Database\QueryException $e): bool
    {
        return str_contains($e->getMessage(), 'database is locked') ||
               str_contains($e->getMessage(), 'deadlock') ||
               str_contains($e->getMessage(), 'locked');
    }

    /**
     * Maneja el reintento para deadlocks.
     */
    private function handleDeadlockRetry(int $retryCount, int $maxRetries): int
    {
        \Illuminate\Support\Facades\DB::rollBack();

        $retryCount++;
        if ($retryCount < $maxRetries) {
            usleep(500000 * $retryCount);
        }

        return $retryCount;
    }
}
