<?php

namespace App\Observers;

use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Repositories\Complementarios\ComplementarioOfertadoRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AspiranteComplementarioObserver
{
    public function __construct(
        private readonly ComplementarioOfertadoRepository $complementarioRepository
    ) {}

    /**
     * Handle the AspiranteComplementario "created" event.
     *
     * Cuando se crea un nuevo aspirante (ya sea por inscripción nueva o agregando existente),
     * verifica si el complementario ha alcanzado su límite de cupos. Si es así, crea
     * automáticamente un nuevo complementario con las mismas características.
     */
    public function created(AspiranteComplementario $aspirante): void
    {
        try {
            // Recargar el complementario desde la base de datos para tener datos frescos
            // Esto asegura que funcione tanto para inscripciones nuevas como para agregar existentes
            $complementario = ComplementarioOfertado::find($aspirante->complementario_id);

            if (!$complementario) {
                Log::warning('No se pudo cargar el complementario para el aspirante', [
                    'aspirante_id' => $aspirante->id,
                    'complementario_id' => $aspirante->complementario_id
                ]);
                return;
            }

            // Solo procesar si el complementario está en estado "Con Oferta" (1)
            if ($complementario->estado !== 1) {
                Log::debug('Complementario no está en estado "Con Oferta", no se creará nuevo complementario', [
                    'complementario_id' => $complementario->id,
                    'estado' => $complementario->estado
                ]);
                return;
            }

            // Contar total de aspirantes del complementario (incluye el recién creado)
            $totalAspirantes = $complementario->aspirantes()->count();

            Log::debug('Verificando cupos del complementario', [
                'complementario_id' => $complementario->id,
                'complementario_codigo' => $complementario->codigo,
                'total_aspirantes' => $totalAspirantes,
                'cupos' => $complementario->cupos,
                'aspirante_id' => $aspirante->id
            ]);

            // Verificar si se alcanzó el límite de cupos
            if ($totalAspirantes >= $complementario->cupos) {
                // Usar transacción para garantizar atomicidad
                DB::transaction(function () use ($complementario, $totalAspirantes) {
                    // Recargar el complementario dentro de la transacción para evitar problemas de concurrencia
                    $complementarioActualizado = ComplementarioOfertado::lockForUpdate()->find($complementario->id);

                    if (!$complementarioActualizado) {
                        throw new \Exception('No se pudo bloquear el complementario para actualización');
                    }

                    // Verificar nuevamente el estado dentro de la transacción
                    if ($complementarioActualizado->estado !== 1) {
                        Log::info('El complementario cambió de estado durante la transacción, cancelando creación de nuevo complementario', [
                            'complementario_id' => $complementarioActualizado->id,
                            'estado_actual' => $complementarioActualizado->estado
                        ]);
                        return;
                    }

                    $estadoCuposLlenosId = $this->obtenerEstadoIdLegacy(2);

                    // Actualizar estado del complementario original a "Cupos Llenos"
                    $complementarioActualizado->update(['estado_id' => $estadoCuposLlenosId]);

                    // Crear nuevo complementario
                    $nuevoComplementario = $this->crearNuevoComplementario($complementarioActualizado);

                    Log::info('Nuevo complementario creado automáticamente al llenarse los cupos', [
                        'complementario_original_id' => $complementarioActualizado->id,
                        'complementario_original_codigo' => $complementarioActualizado->codigo,
                        'nuevo_complementario_id' => $nuevoComplementario->id,
                        'nuevo_complementario_codigo' => $nuevoComplementario->codigo,
                        'total_aspirantes' => $totalAspirantes,
                        'cupos' => $complementarioActualizado->cupos,
                        'origen' => 'Observer - AspiranteComplementario created'
                    ]);
                });
            }
        } catch (\Exception $e) {
            Log::error('Error en AspiranteComplementarioObserver al verificar cupos', [
                'aspirante_id' => $aspirante->id,
                'complementario_id' => $aspirante->complementario_id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Crear un nuevo complementario basado en uno existente
     *
     * @param ComplementarioOfertado $complementarioOriginal
     * @return ComplementarioOfertado
     */
    private function crearNuevoComplementario(ComplementarioOfertado $complementarioOriginal): ComplementarioOfertado
    {
        // Generar código único para el nuevo complementario
        $nuevoCodigo = $this->generarCodigoUnico($complementarioOriginal->codigo);

        // Crear el nuevo complementario con los mismos datos básicos
        $nuevoComplementario = ComplementarioOfertado::create([
            'codigo' => $nuevoCodigo,
            'nombre' => $complementarioOriginal->nombre,
            'justificacion' => $complementarioOriginal->justificacion,
            'requisitos_ingreso' => $complementarioOriginal->requisitos_ingreso,
            'duracion' => $complementarioOriginal->duracion,
            'cupos' => $complementarioOriginal->cupos,
            'estado_id' => $this->obtenerEstadoIdLegacy(1), // Estado "Con Oferta"
            'modalidad_id' => $complementarioOriginal->modalidad_id,
            'jornada_id' => $complementarioOriginal->jornada_id,
            'ambiente_id' => $complementarioOriginal->ambiente_id,
            'user_create_id' => Auth::id() ?? 1, // Usuario autenticado o sistema
            'user_edit_id' => Auth::id() ?? 1, // Usuario autenticado o sistema
        ]);

        // Copiar todas las relaciones
        $this->copiarRelaciones($complementarioOriginal, $nuevoComplementario);

        return $nuevoComplementario;
    }

    /**
     * Copiar todas las relaciones del complementario original al nuevo
     *
     * @param ComplementarioOfertado $original
     * @param ComplementarioOfertado $nuevo
     */
    private function copiarRelaciones(ComplementarioOfertado $original, ComplementarioOfertado $nuevo): void
    {
        // Cargar relaciones si no están cargadas
        $original->loadMissing([
            'diasFormacion',
            'competencias',
            'raps',
            'guiasAprendizaje'
        ]);

        // Copiar días de formación con sus pivots (hora_inicio, hora_fin)
        if ($original->diasFormacion->isNotEmpty()) {
            $diasFormacionData = $original->diasFormacion->mapWithKeys(function ($dia) {
                return [
                    $dia->id => [
                        'hora_inicio' => $dia->pivot->hora_inicio,
                        'hora_fin' => $dia->pivot->hora_fin,
                    ]
                ];
            })->toArray();

            $nuevo->diasFormacion()->sync($diasFormacionData);
        }

        // Copiar competencias con sus pivots (user_create_id, user_edit_id)
        if ($original->competencias->isNotEmpty()) {
            $competenciasData = $original->competencias->mapWithKeys(function ($competencia) {
                return [
                    $competencia->id => [
                        'user_create_id' => $competencia->pivot->user_create_id ?? null,
                        'user_edit_id' => $competencia->pivot->user_edit_id ?? null,
                    ]
                ];
            })->toArray();

            $nuevo->competencias()->sync($competenciasData);
        }

        // Copiar resultados de aprendizaje (RAPs) con sus pivots
        if ($original->raps->isNotEmpty()) {
            $rapsData = $original->raps->mapWithKeys(function ($rap) {
                return [
                    $rap->id => [
                        'user_create_id' => $rap->pivot->user_create_id ?? null,
                        'user_edit_id' => $rap->pivot->user_edit_id ?? null,
                    ]
                ];
            })->toArray();

            $nuevo->raps()->sync($rapsData);
        }

        // Copiar guías de aprendizaje con sus pivots
        if ($original->guiasAprendizaje->isNotEmpty()) {
            $guiasData = $original->guiasAprendizaje->mapWithKeys(function ($guia) {
                return [
                    $guia->id => [
                        'user_create_id' => $guia->pivot->user_create_id ?? null,
                        'user_edit_id' => $guia->pivot->user_edit_id ?? null,
                    ]
                ];
            })->toArray();

            $nuevo->guiasAprendizaje()->sync($guiasData);
        }
    }

    /**
     * Generar un código único para el nuevo complementario
     *
     * Si el código original es COMP0001, genera COMP0001-2, COMP0001-3, etc.
     * Si ya existe un código con sufijo, incrementa el número.
     *
     * @param string $codigoOriginal
     * @return string
     */
    private function generarCodigoUnico(string $codigoOriginal): string
    {
        // Si el código ya tiene un sufijo (ej: COMP0001-2), extraer la base
        if (preg_match('/^(.+)-(\d+)$/', $codigoOriginal, $matches)) {
            $codigoBase = $matches[1];
            $ultimoNumero = (int) $matches[2];
        } else {
            $codigoBase = $codigoOriginal;
            $ultimoNumero = 1;
        }

        // Buscar el siguiente número disponible
        $siguienteNumero = $ultimoNumero + 1;
        $nuevoCodigo = "{$codigoBase}-{$siguienteNumero}";

        // Verificar que el código no exista (por si acaso)
        while (ComplementarioOfertado::where('codigo', $nuevoCodigo)->exists()) {
            $siguienteNumero++;
            $nuevoCodigo = "{$codigoBase}-{$siguienteNumero}";
        }

        return $nuevoCodigo;
    }

    private function obtenerEstadoIdLegacy(int $valorLegacy): int
    {
        $estadoId = $this->complementarioRepository->getEstadoIdByLegacyValue($valorLegacy);

        if (!$estadoId) {
            throw new RuntimeException(sprintf(
                'No se encontró el parámetro de estado para el valor legacy %d',
                $valorLegacy
            ));
        }

        return $estadoId;
    }

    /**
     * Handle the AspiranteComplementario "updated" event.
     */
    public function updated(AspiranteComplementario $aspirante): void
    {
        // No se requiere acción al actualizar
    }

    /**
     * Handle the AspiranteComplementario "deleted" event.
     */
    public function deleted(AspiranteComplementario $aspirante): void
    {
        // No se requiere acción al eliminar
    }

    /**
     * Handle the AspiranteComplementario "restored" event.
     */
    public function restored(AspiranteComplementario $aspirante): void
    {
        // No se requiere acción al restaurar
    }

    /**
     * Handle the AspiranteComplementario "force deleted" event.
     */
    public function forceDeleted(AspiranteComplementario $aspirante): void
    {
        // No se requiere acción al eliminar permanentemente
    }
}

