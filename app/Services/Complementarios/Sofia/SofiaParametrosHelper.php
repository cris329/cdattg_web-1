<?php

namespace App\Services\Complementarios\Sofia;

use App\Models\Parametro;
use Illuminate\Support\Facades\Cache;

class SofiaParametrosHelper
{
    private const CACHE_KEY = 'sofia_parametros_ids';
    private const CACHE_TTL = 3600; // 1 hora

    /**
     * Obtener ID del parámetro "NO REGISTRADO"
     */
    public static function getNoRegistradoId(): ?int
    {
        return self::getParametroId('NO REGISTRADO');
    }

    /**
     * Obtener ID del parámetro "REGISTRADO"
     */
    public static function getRegistradoId(): ?int
    {
        return self::getParametroId('REGISTRADO');
    }

    /**
     * Obtener ID del parámetro "REQUIERE CAMBIO"
     */
    public static function getRequiereCambioId(): ?int
    {
        return self::getParametroId('REQUIERE CAMBIO');
    }

    /**
     * Obtener ID del parámetro "VALIDAR"
     */
    public static function getValidarId(): ?int
    {
        return self::getParametroId('VALIDAR');
    }

    /**
     * Obtener ID del parámetro "EXITOSO"
     */
    public static function getExitosoId(): ?int
    {
        return self::getParametroId('EXITOSO');
    }

    /**
     * Obtener ID del parámetro "ERROR"
     */
    public static function getErrorId(): ?int
    {
        return self::getParametroId('ERROR');
    }

    /**
     * Obtener ID del parámetro "ADVERTENCIA"
     */
    public static function getAdvertenciaId(): ?int
    {
        return self::getParametroId('ADVERTENCIA');
    }

    /**
     * Obtener ID del parámetro "PENDING"
     */
    public static function getPendingId(): ?int
    {
        return self::getParametroId('PENDING');
    }

    /**
     * Obtener ID del parámetro "PROCESSING"
     */
    public static function getProcessingId(): ?int
    {
        return self::getParametroId('PROCESSING');
    }

    /**
     * Obtener ID del parámetro "COMPLETED"
     */
    public static function getCompletedId(): ?int
    {
        return self::getParametroId('COMPLETED');
    }

    /**
     * Obtener ID del parámetro "FAILED"
     */
    public static function getFailedId(): ?int
    {
        return self::getParametroId('FAILED');
    }

    /**
     * Obtener todos los IDs de parámetros de Sofía
     */
    public static function getAllIds(): array
    {
        // En testing, no usar cache para evitar problemas
        if (app()->environment('testing')) {
            return self::obtenerIdsDirectamente();
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::obtenerIdsDirectamente();
        });
    }

    /**
     * Obtener IDs directamente de la base de datos
     */
    private static function obtenerIdsDirectamente(): array
    {
        $parametros = [
            'NO REGISTRADO',
            'REGISTRADO',
            'REQUIERE CAMBIO',
            'VALIDAR',
            'EXITOSO',
            'ERROR',
            'ADVERTENCIA',
            'PENDING',
            'PROCESSING',
            'COMPLETED',
            'FAILED',
        ];

        $ids = [];
        foreach ($parametros as $nombre) {
            $parametro = Parametro::where('name', $nombre)->first();
            if ($parametro) {
                $ids[$nombre] = $parametro->id;
            }
        }

        // Si faltan parámetros, intentar crearlos (solo en entorno de testing)
        if (app()->environment('testing')) {
            $faltantes = array_diff($parametros, array_keys($ids));
            if (!empty($faltantes)) {
                self::crearParametrosSiNoExisten();
                // Buscar nuevamente después de crearlos
                foreach ($parametros as $nombre) {
                    if (!isset($ids[$nombre])) {
                        $parametro = Parametro::where('name', $nombre)->first();
                        if ($parametro) {
                            $ids[$nombre] = $parametro->id;
                        }
                    }
                }
            }
        }

        return $ids;
    }

    /**
     * Crear parámetros de Sofía si no existen (público para testing)
     */
    public static function crearParametrosSiNoExisten(): void
    {
        // En testing, usar null para user_create_id para evitar problemas con foreign keys
        $userId = null;
        
        try {
            $temaEstados = \App\Models\Tema::updateOrCreate(
                ['name' => 'ESTADOS SOFIA'],
                ['status' => 1, 'user_create_id' => $userId]
            );

            $temaAcciones = \App\Models\Tema::updateOrCreate(
                ['name' => 'ACCIONES SOFIA'],
                ['status' => 1, 'user_create_id' => $userId]
            );

            $temaResultados = \App\Models\Tema::updateOrCreate(
                ['name' => 'RESULTADOS VALIDACION SOFIA'],
                ['status' => 1, 'user_create_id' => $userId]
            );

            $temaProgreso = \App\Models\Tema::updateOrCreate(
                ['name' => 'ESTADOS PROGRESO SOFIA'],
                ['status' => 1, 'user_create_id' => $userId]
            );

        // Crear parámetros de estados
        $parametrosEstados = ['NO REGISTRADO', 'REGISTRADO', 'REQUIERE CAMBIO'];
        foreach ($parametrosEstados as $nombre) {
            $parametro = Parametro::updateOrCreate(
                ['name' => $nombre],
                [
                    'status' => 1,
                    'user_create_id' => $userId
                ]
            );
            // Asegurar que el parámetro esté relacionado con el tema
            $temaEstados->parametros()->syncWithoutDetaching([
                $parametro->id => ['status' => 1, 'user_create_id' => $userId]
            ]);
        }

            // Crear parámetro de acción
            $parametroAccion = Parametro::updateOrCreate(
                ['name' => 'VALIDAR'],
                ['status' => 1, 'user_create_id' => $userId]
            );
            $temaAcciones->parametros()->syncWithoutDetaching([
                $parametroAccion->id => ['status' => 1, 'user_create_id' => $userId]
            ]);

            // Crear parámetros de resultados
            $parametrosResultados = ['EXITOSO', 'ERROR', 'ADVERTENCIA'];
            foreach ($parametrosResultados as $nombre) {
                $parametro = Parametro::updateOrCreate(
                    ['name' => $nombre],
                    ['status' => 1, 'user_create_id' => $userId]
                );
                $temaResultados->parametros()->syncWithoutDetaching([
                    $parametro->id => ['status' => 1, 'user_create_id' => $userId]
                ]);
            }

        // Crear parámetros de progreso
        $parametrosProgreso = ['PENDING', 'PROCESSING', 'COMPLETED', 'FAILED'];
        foreach ($parametrosProgreso as $nombre) {
            $parametro = Parametro::updateOrCreate(
                ['name' => $nombre],
                [
                    'status' => 1,
                    'user_create_id' => $userId
                ]
            );
            // Asegurar que el parámetro esté relacionado con el tema
            $temaProgreso->parametros()->syncWithoutDetaching([
                $parametro->id => ['status' => 1, 'user_create_id' => $userId]
            ]);
        }
        
            // Limpiar cache después de crear parámetros
            self::clearCache();
        } catch (\Exception $e) {
            \Log::error("Error al crear parámetros de Sofía: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Obtener ID de un parámetro por nombre
     */
    private static function getParametroId(string $nombre): ?int
    {
        // En testing, siempre buscar directamente en la base de datos
        if (app()->environment('testing')) {
            $parametro = Parametro::where('name', $nombre)->first();
            if ($parametro) {
                return $parametro->id;
            }
            
            // Si no existe, intentar crearlo
            try {
                self::crearParametrosSiNoExisten();
                // Buscar nuevamente después de crear
                $parametro = Parametro::where('name', $nombre)->first();
                if ($parametro) {
                    self::clearCache();
                    return $parametro->id;
                }
            } catch (\Exception $e) {
                \Log::error("Error al crear parámetros de Sofía en testing: " . $e->getMessage(), [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            return null;
        }
        
        // En producción/desarrollo, usar el cache
        $ids = self::getAllIds();
        
        // Si el parámetro existe, retornarlo
        if (isset($ids[$nombre]) && $ids[$nombre] !== null) {
            return $ids[$nombre];
        }
        
        // Si no existe en cache, buscar directamente
        $parametro = Parametro::where('name', $nombre)->first();
        if ($parametro) {
            self::clearCache();
            return $parametro->id;
        }
        
        return null;
    }

    /**
     * Limpiar cache de parámetros
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}

