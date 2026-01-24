<?php

namespace App\Services\Complementarios;

use App\Exceptions\ProgramaNoEncontradoException;
use App\Exceptions\ProcesarDocumentoIdentidadException;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Persona;
use App\Repositories\Complementarios\AspiranteComplementarioRepository;
use App\Repositories\Complementarios\ComplementarioOfertadoRepository;
use App\Repositories\PersonaRepository;
use App\Services\Complementarios\AspiranteDocumentoService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class AspiranteManagementService
{
    private const PROGRAMA_NO_ENCONTRADO = 'Programa no encontrado.';
    private const PROGRAMA_NO_ENCONTRADO_SIN_PUNTO = 'Programa no encontrado';

    public function __construct(
        private readonly AspiranteComplementarioRepository $aspiranteRepository,
        private readonly ComplementarioOfertadoRepository $programaRepository,
        private readonly PersonaRepository $personaRepository,
        private readonly AspiranteDocumentoService $documentoService
    ) {}

    /**
     * Obtener programas con conteo de aspirantes para gestión
     */
    public function obtenerProgramasParaGestion(): Collection
    {
        return $this->programaRepository->getAllWithAspirantesCount(['modalidad.parametro', 'jornada', 'diasFormacion']);
    }

    /**
     * Obtener aspirantes de un programa específico por nombre
     */
    public function obtenerAspirantesPorPrograma(string $cursoNombre): array
    {
        $programa = $this->programaRepository->findByNombre($cursoNombre);

        if (!$programa) {
            abort(404, self::PROGRAMA_NO_ENCONTRADO_SIN_PUNTO);
        }

        // Usar el método obtenerAspirantesPorProgramaId que ya maneja las relaciones
        return $this->obtenerAspirantesPorProgramaId($programa->id);
    }

    /**
     * Obtener aspirantes de un programa específico por ID
     */
    public function obtenerAspirantesPorProgramaId(int $programaId): array
    {
        // Cargar programa con relaciones básicas
        $programa = $this->programaRepository->findWithRelations($programaId, ['modalidad', 'jornada', 'diasFormacion']);

        if (!$programa) {
            abort(404, self::PROGRAMA_NO_ENCONTRADO_SIN_PUNTO);
        }

        // Cargar relación parametro de modalidad desde el catálogo si existe
        // Hacerlo de forma segura para evitar errores si la relación no existe
        try {
            if ($programa->catalogo_id) {
                $programa->loadMissing(['catalogo.modalidad.parametro']);
            }
        } catch (\Exception $e) {
            // Si falla cargar la relación, continuar sin ella (la vista maneja esto con optional())
        }

        $aspirantes = $this->aspiranteRepository->findByPrograma($programaId, ['persona', 'complementario']);

        // Verificar progreso de validación existente
        // Solo verificar si no estamos en un entorno de testing para evitar deadlocks
        $existingProgress = null;
        if (!app()->environment('testing')) {
            try {
                $existingProgress = \App\Models\Complementarios\SofiaValidationProgress::where('complementario_id', $programaId)
                    ->whereIn('status', [284, 285]) // PENDING (284) o PROCESSING (285)
                    ->first();
            } catch (\Exception $e) {
                // Si hay un error, continuar sin progreso
                \Log::debug("No se pudo verificar progreso de validación: " . $e->getMessage());
            }
        }

        return [
            'programa' => $programa,
            'aspirantes' => $aspirantes,
            'existingProgress' => $existingProgress,
        ];
    }

    /**
     * Agregar aspirante existente a un programa
     *
     * Nota: Las validaciones de existencia del programa, persona e inscripción duplicada
     * también se realizan en StoreAspiranteRequest (FormRequest), pero aquí se valida
     * nuevamente como capa de seguridad adicional y para manejar excepciones de BD.
     *
     * @param int $complementarioId ID del programa complementario
     * @param string $numeroDocumento Número de documento de la persona
     * @param string|null $observaciones Observaciones opcionales
     * @return array Resultado de la operación
     */
    public function agregarAspirante(int $complementarioId, string $numeroDocumento, ?string $observaciones = null): array
    {
        try {
            // Validar que el programa exista (validación básica, la validación completa está en FormRequest)
            $programa = $this->programaRepository->findWithRelations($complementarioId);
            if (!$programa) {
                return $this->createErrorResponse(self::PROGRAMA_NO_ENCONTRADO);
            }

            $persona = $this->personaRepository->findByNumeroDocumento($numeroDocumento);
            if (!$persona) {
                return $this->createErrorResponse(
                    'No se encontró ninguna persona registrada con el número de documento "' . $numeroDocumento . '".'
                );
            }

            // Validar que el aspirante no esté ya inscrito en el programa
            $existeInscripcion = $this->aspiranteRepository->existeInscripcion($persona->id, $complementarioId);

            if ($existeInscripcion) {
                return $this->createErrorResponse(
                    'El aspirante ya está en este programa complementario.'
                );
            }

            // Usar observaciones proporcionadas o valor por defecto
            $observacionesFinal = $observaciones ?? 'Agregado manualmente desde gestión de aspirantes';

            $aspirante = $this->aspiranteRepository->create([
                'persona_id' => $persona->id,
                'complementario_id' => $complementarioId,
                'estado' => 1,
                'observaciones' => $observacionesFinal
            ]);

            Log::info('Aspirante agregado exitosamente', [
                'complementario_id' => $complementarioId,
                'persona_id' => $persona->id,
                'numero_documento' => $numeroDocumento,
                'user_id' => Auth::id()
            ]);

            $resultado = $this->createSuccessResponse(
                'Aspirante agregado exitosamente. ' . $persona->primer_nombre . ' ' .
                $persona->primer_apellido . ' ha sido inscrito en el programa.'
            );
            $resultado['aspirante'] = $aspirante;

            return $resultado;

        } catch (\Exception $e) {
            Log::error('Error agregando aspirante: ' . $e->getMessage(), [
                'complementario_id' => $complementarioId,
                'numero_documento' => $numeroDocumento,
                'exception' => $e->getTraceAsString()
            ]);

            return $this->createErrorResponse('Error interno del servidor. Por favor intente nuevamente.');
        }
    }

    /**
     * Rechazar aspirante (cambiar estado a rechazado)
     *
     * @param int $complementarioId ID del programa complementario
     * @param int $aspiranteId ID del aspirante a rechazar
     * @param string|null $motivoRechazo Motivo del rechazo (opcional)
     * @param string|null $observaciones Observaciones adicionales (opcional)
     * @return array Resultado de la operación
     */
    public function rechazarAspirante(int $complementarioId, int $aspiranteId, ?string $motivoRechazo = null, ?string $observaciones = null): array
    {
        try {
            $errorResponse = $this->validarRechazarAspirante($complementarioId, $aspiranteId);
            if ($errorResponse !== null) {
                return $errorResponse;
            }

            $aspirantes = $this->aspiranteRepository->findByPrograma($complementarioId);
            $aspirante = $aspirantes->where('id', $aspiranteId)->first();
            
            if (!$aspirante) {
                return [
                    'success' => false,
                    'message' => 'Aspirante no encontrado.',
                    'status_code' => 200
                ];
            }
            
            // Cargar relación persona si no está cargada
            if (!$aspirante->relationLoaded('persona')) {
                $aspirante->load('persona');
            }

            $personaNombre = $aspirante->persona->primer_nombre . ' ' . $aspirante->persona->primer_apellido;
            $numeroDocumento = $aspirante->persona->numero_documento;

            // Construir observaciones finales
            $observacionesFinal = $observaciones ?? $aspirante->observaciones;
            if ($motivoRechazo !== null) {
                $observacionesFinal = ($observacionesFinal ? $observacionesFinal . ' | ' : '') . 'Motivo rechazo: ' . $motivoRechazo;
            }

            $this->aspiranteRepository->update($aspirante, [
                'estado' => 4,
                'observaciones' => $observacionesFinal
            ]);

            Log::info('Aspirante rechazado exitosamente', [
                'aspirante_id' => $aspiranteId,
                'complementario_id' => $complementarioId,
                'persona_id' => $aspirante->persona_id,
                'user_id' => Auth::id()
            ]);

            return $this->createSuccessResponse(
                'Aspirante rechazado exitosamente. ' . $personaNombre . ' (' . $numeroDocumento . ') ha sido marcado como rechazado en el programa.'
            );

        } catch (\Exception $e) {
            Log::error('Error rechazando aspirante: ' . $e->getMessage(), [
                'complementario_id' => $complementarioId,
                'aspirante_id' => $aspiranteId,
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno del servidor. Por favor intente nuevamente.',
                'status_code' => 500
            ];
        }
    }

    /**
     * Obtener estadísticas básicas de un programa
     */
    public function obtenerEstadisticasPrograma(int $programaId): array
    {
        $programa = $this->programaRepository->findWithRelations($programaId);

        if (!$programa) {
            throw new ProgramaNoEncontradoException(self::PROGRAMA_NO_ENCONTRADO_SIN_PUNTO);
        }

        $totalAspirantes = $this->aspiranteRepository->countByPrograma($programaId);
        $aspirantesActivos = $this->aspiranteRepository->countByEstado($programaId, 1);
        $aspirantesAceptados = $this->aspiranteRepository->countByEstado($programaId, 3);

        return [
            'total_aspirantes' => $totalAspirantes,
            'aspirantes_activos' => $aspirantesActivos,
            'aspirantes_aceptados' => $aspirantesAceptados,
            'cupos_disponibles' => max(0, $programa->cupos - $totalAspirantes),
        ];
    }

    /**
     * Validar documentos de aspirantes
     */
    public function validarDocumentos(int $complementarioId, AspiranteDocumentoService $documentoService): array
    {
        try {
            $errorResponse = $this->validarDocumentosPrecondiciones($complementarioId);
            if ($errorResponse !== null) {
                return $errorResponse;
            }

            $aspirantes = $this->aspiranteRepository->findByPrograma($complementarioId, ['persona.tipoDocumento']);
            $files = $documentoService->getGoogleDriveFiles();
            $resultados = $this->procesarValidacionDocumentos($aspirantes, $files, $documentoService);

            Log::info("Validación de documentos completada", [
                'complementario_id' => $complementarioId,
                'total' => $resultados['total'],
                'con_documento' => $resultados['con_documento'],
                'sin_documento' => $resultados['sin_documento'],
                'errores' => $resultados['errores']
            ]);

            return [
                'success' => true,
                'message' => "Validación completada. Total: {$resultados['total']}, " .
                    "Con documento: {$resultados['con_documento']}, " .
                    "Sin documento: {$resultados['sin_documento']}" .
                    ($resultados['errores'] > 0 ? ", Errores: {$resultados['errores']}" : ""),
                'total' => $resultados['total'],
                'con_documento' => $resultados['con_documento'],
                'sin_documento' => $resultados['sin_documento'],
                'errores' => $resultados['errores']
            ];

        } catch (\Exception $e) {
            Log::error('Error validando documentos: ' . $e->getMessage(), [
                'complementario_id' => $complementarioId,
                'user_id' => Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage(),
                'status_code' => 500
            ];
        }
    }

    /**
     * Procesar validación de documentos
     */
    private function procesarValidacionDocumentos(Collection $aspirantes, $files, AspiranteDocumentoService $documentoService): array
    {
        $totalAspirantes = $aspirantes->count();
        $conDocumento = 0;
        $sinDocumento = 0;
        $errores = 0;

        foreach ($aspirantes as $aspirante) {
            try {
                $persona = $aspirante->persona;
                $patron = $documentoService->construirPatronBusqueda($persona);
                $tieneDocumento = $documentoService->buscarDocumentoEnGoogleDrive($files, $patron);

                // Actualizar estado del documento en la persona
                $this->personaRepository->updateDocumentoStatus($persona, $tieneDocumento);

                if ($tieneDocumento) {
                    $conDocumento++;
                } else {
                    $sinDocumento++;
                }

            } catch (\Exception $e) {
                $errores++;
                Log::error("Error validando documento para aspirante {$aspirante->id}", [
                    'aspirante_id' => $aspirante->id,
                    'persona_id' => $aspirante->persona_id,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return [
            'total' => $totalAspirantes,
            'con_documento' => $conDocumento,
            'sin_documento' => $sinDocumento,
            'errores' => $errores
        ];
    }


    /**
     * Validar precondiciones para rechazar aspirante
     */
    private function validarRechazarAspirante(int $complementarioId, int $aspiranteId): ?array
    {
        $errorResponse = null;

        if (!Gate::allows('ELIMINAR ASPIRANTE COMPLEMENTARIO')) {
            $errorResponse = [
                'success' => false,
                'message' => 'No tiene permisos para rechazar aspirantes.',
                'status_code' => 403
            ];
        }

        if ($errorResponse === null) {
            $programa = $this->programaRepository->findWithRelations($complementarioId);
            if (!$programa) {
                $errorResponse = [
                    'success' => false,
                    'message' => self::PROGRAMA_NO_ENCONTRADO,
                    'status_code' => 200
                ];
            }
        }

        if ($errorResponse === null) {
            $aspirantes = $this->aspiranteRepository->findByPrograma($complementarioId);
            $aspirante = $aspirantes->where('id', $aspiranteId)->first();

            if (!$aspirante) {
                $errorResponse = [
                    'success' => false,
                    'message' => 'Aspirante no encontrado.',
                    'status_code' => 200
                ];
            }
        }

        return $errorResponse;
    }

    /**
     * Validar precondiciones para validar documentos
     */
    private function validarDocumentosPrecondiciones(int $complementarioId): ?array
    {
        $programa = $this->programaRepository->findWithRelations($complementarioId);
        if (!$programa) {
            return $this->createErrorResponse(self::PROGRAMA_NO_ENCONTRADO);
        }

        $errorResponse = null;
        $aspirantes = $this->aspiranteRepository->findByPrograma($complementarioId, ['persona.tipoDocumento']);
        if ($aspirantes->isEmpty()) {
            $errorResponse = $this->createErrorResponse('No hay aspirantes en este programa para validar documentos.');
        }

        return $errorResponse;
    }

    /**
     * Guardar documento de identidad para un aspirante existente.
     */
    public function almacenarDocumentoIdentidad(AspiranteComplementario $aspirante, Persona $persona, UploadedFile $archivo): void
    {
        try {
            $upload = $this->documentoService->subirDocumentoIdentidad($persona, $archivo);

            Log::info('Documento de identidad cargado manualmente', [
                'aspirante_id' => $aspirante->id,
                'persona_id' => $persona->id,
                'file_name' => $upload['name'],
            ]);

            $this->aspiranteRepository->update($aspirante, [
                'documento_identidad_path' => $upload['path'],
                'documento_identidad_nombre' => $upload['name'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error al guardar el documento de identidad del aspirante', [
                'aspirante_id' => $aspirante->id,
                'persona_id' => $persona->id,
                'exception' => $e->getMessage(),
            ]);

            $this->aspiranteRepository->update($aspirante, ['estado' => 1]);

            throw new ProcesarDocumentoIdentidadException('Error al procesar el documento de identidad');
        }
    }

    /**
     * Crear respuesta de error
     */
    private function createErrorResponse(string $message): array
    {
        return [
            'success' => false,
            'message' => $message
        ];
    }

    /**
     * Crear respuesta de éxito
     */
    private function createSuccessResponse(string $message): array
    {
        return [
            'success' => true,
            'message' => $message
        ];
    }
}
