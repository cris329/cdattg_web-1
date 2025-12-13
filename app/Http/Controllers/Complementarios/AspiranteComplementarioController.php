<?php

namespace App\Http\Controllers\Complementarios;

use App\Http\Controllers\Controller;
use App\Http\Requests\Complementarios\StoreAspiranteRequest;
use App\Http\Requests\Complementarios\UpdateAspiranteRequest;
use App\Http\Requests\Complementarios\CreateAspiranteRequest;
use App\Http\Requests\Complementarios\RechazarAspiranteRequest;
use App\Http\Requests\Complementarios\BuscarPersonaRequest;
use App\Services\Complementarios\AspiranteManagementService;
use App\Services\Complementarios\AspiranteExportService;
use App\Services\Complementarios\AspiranteDocumentoService;
use App\Services\Complementarios\ComplementarioService;
use App\Services\PersonaService;
use App\Repositories\Complementarios\AspiranteComplementarioRepository;
use App\Repositories\Complementarios\ComplementarioOfertadoRepository;
use App\Repositories\TemaRepository;
use App\Models\Pais;
use App\Models\Departamento;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AspiranteComplementarioController extends Controller
{
    /**
     * Mensaje de error genérico para errores internos del servidor
     */
    private const ERROR_MENSAJE_SERVIDOR = 'Error interno del servidor. Por favor intente nuevamente.';

    public function __construct(
        private readonly AspiranteManagementService $aspiranteManagementService,
        private readonly AspiranteExportService $exportService,
        private readonly AspiranteDocumentoService $documentoService,
        private readonly PersonaService $personaService,
        private readonly AspiranteComplementarioRepository $aspiranteRepository,
        private readonly ComplementarioOfertadoRepository $programaRepository,
        private readonly ComplementarioService $complementarioService,
        private readonly TemaRepository $temaRepository
    ) {}

    /**
     * Lista de programas complementarios (gestión de aspirantes)
     */
    public function index(): View
    {
        $programas = $this->aspiranteManagementService->obtenerProgramasParaGestion();

        return view('complementarios.aspirantes.index', compact('programas'));
    }

    /**
     * Mostrar aspirantes de un programa específico (por nombre)
     */
    public function verAspirantes(string $curso): View
    {
        $data = $this->aspiranteManagementService->obtenerAspirantesPorPrograma($curso);

        return view('complementarios.aspirantes.programa', $data);
    }

    /**
     * Mostrar aspirantes de un programa específico (por ID)
     */
    public function programa(int $programa): View
    {
        try {
            $data = $this->aspiranteManagementService->obtenerAspirantesPorProgramaId($programa);
            return view('complementarios.aspirantes.programa', $data);
        } catch (\Exception $e) {
            \Log::error("Error en programa() método: " . $e->getMessage(), [
                'programa_id' => $programa,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }


    /**
     * Eliminar/rechazar aspirante de un programa complementario (cambiar estado a rechazado)
     *
     * Este método sigue las convenciones de Laravel Resource Controller (destroy).
     * Implementa el caso de uso RF-ASP-004: Rechazar Aspirante.
     *
     * @param RechazarAspiranteRequest $request Request validado (opcional: motivo_rechazo, observaciones)
     * @param int $programa ID del programa complementario
     * @param int $aspirante ID del aspirante a rechazar
     * @return JsonResponse Respuesta JSON con resultado de la operación
     */
    public function destroy(RechazarAspiranteRequest $request, int $programa, int $aspirante): JsonResponse
    {
        $validated = $request->validated();
        $motivoRechazo = $validated['motivo_rechazo'] ?? null;
        $observaciones = $validated['observaciones'] ?? null;

        $resultado = $this->aspiranteManagementService->rechazarAspirante(
            $programa,
            $aspirante,
            $motivoRechazo,
            $observaciones
        );

        $statusCode = $resultado['status_code'] ?? 200;
        unset($resultado['status_code']);

        return response()->json($resultado, $statusCode);
    }

    /**
     * Exportar aspirantes a Excel
     */
    public function exportarAspirantesExcel(int $complementarioId): StreamedResponse|JsonResponse
    {
        try {
            return $this->exportService->exportarAspirantesExcel($complementarioId);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error exportando aspirantes a Excel: ' . $e->getMessage(), [
                'complementario_id' => $complementarioId,
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el archivo Excel. Por favor intente nuevamente.'
            ], 500);
        }
    }

    /**
     * Descargar cédulas de aspirantes en un archivo PDF combinado
     */
    public function descargarCedulas(int $complementarioId)
    {
        try {
            return $this->exportService->descargarCedulas($complementarioId);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Validar documentos de aspirantes en Google Drive
     */
    public function validarDocumentos(int $complementarioId): JsonResponse
    {
        $resultado = $this->aspiranteManagementService->validarDocumentos($complementarioId, $this->documentoService);

        $statusCode = $resultado['status_code'] ?? 200;
        unset($resultado['status_code']);

        return response()->json($resultado, $statusCode);
    }

    /**
     * Buscar persona por número de documento
     */
    public function buscarPersona(BuscarPersonaRequest $request): JsonResponse
    {
        $persona = $this->personaService->buscarPorDocumento(trim($request->validated()['numero_documento']));

        if (!$persona) {
            return response()->json([
                'success' => false,
                'found' => false,
                'message' => 'Persona no encontrada.'
            ]);
        }

        $persona->loadMissing(['tipoDocumento', 'tipoGenero', 'pais', 'departamento', 'municipio', 'caracterizacionesComplementarias']);

        return response()->json([
            'success' => true,
            'found' => true,
            'persona' => [
                'id' => $persona->id,
                'tipo_documento_id' => $persona->tipo_documento,
                'tipo_documento' => $persona->tipoDocumento ? $persona->tipoDocumento->parametro->name : null,
                'numero_documento' => $persona->numero_documento,
                'primer_nombre' => $persona->primer_nombre,
                'segundo_nombre' => $persona->segundo_nombre,
                'primer_apellido' => $persona->primer_apellido,
                'segundo_apellido' => $persona->segundo_apellido,
                'fecha_nacimiento' => $persona->fecha_nacimiento,
                'genero_id' => $persona->genero,
                'genero' => $persona->tipoGenero ? $persona->tipoGenero->parametro->name : null,
                'telefono' => $persona->telefono,
                'celular' => $persona->celular,
                'email' => $persona->email,
                'pais_id' => $persona->pais_id,
                'pais' => $persona->pais ? $persona->pais->pais : null,
                'departamento_id' => $persona->departamento_id,
                'departamento' => $persona->departamento ? $persona->departamento->departamento : null,
                'municipio_id' => $persona->municipio_id,
                'municipio' => $persona->municipio ? $persona->municipio->municipio : null,
                'direccion' => $persona->direccion,
                'caracterizaciones' => $persona->caracterizacionesComplementarias->pluck('id')->toArray(),
            ]
        ]);
    }

    /**
     * Mostrar formulario para crear nuevo aspirante
     */
    public function create(int $programa): View
    {
        $data = $this->aspiranteManagementService->obtenerAspirantesPorProgramaId($programa);

        // Preparar datos adicionales para el formulario
        $temaTipoDocumento = $this->temaRepository->obtenerTiposDocumento();
        $tiposDocumento = $this->complementarioService->getTiposDocumento();
        $documentos = (object) [
            'tema' => $temaTipoDocumento,
            'parametros' => $temaTipoDocumento && $temaTipoDocumento->parametros->count() > 0
                ? $temaTipoDocumento->parametros()->where('parametros_temas.status', 1)->orderBy('parametros.name')->get(['parametros.id', 'parametros.name'])
                : $tiposDocumento
        ];

        $temaGenero = $this->temaRepository->obtenerGeneros();
        $generosFallback = $this->complementarioService->getGeneros();
        $generos = ($temaGenero && isset($temaGenero->parametros) && $temaGenero->parametros->count() > 0)
            ? $temaGenero
            : (object) ['parametros' => $generosFallback];

        $temaCaracterizacion = $this->temaRepository->obtenerCaracterizacionesComplementarias();
        $caracterizaciones = ($temaCaracterizacion && isset($temaCaracterizacion->parametros) && $temaCaracterizacion->parametros->count() > 0)
            ? $temaCaracterizacion
            : (object) ['parametros' => collect()];

        $temaVia = $this->temaRepository->obtenerVias();
        $vias = ($temaVia && isset($temaVia->parametros) && $temaVia->parametros->count() > 0)
            ? $temaVia
            : (object) ['parametros' => collect()];

        $temaLetra = $this->temaRepository->obtenerLetras();
        $letras = ($temaLetra && isset($temaLetra->parametros) && $temaLetra->parametros->count() > 0)
            ? $temaLetra
            : (object) ['parametros' => collect()];

        $temaCardinal = $this->temaRepository->obtenerCardinales();
        $cardinales = ($temaCardinal && isset($temaCardinal->parametros) && $temaCardinal->parametros->count() > 0)
            ? $temaCardinal
            : (object) ['parametros' => collect()];

        // Obtener tema de nivel de escolaridad
        $temaNivelEscolaridad = $this->temaRepository->obtenerNivelEscolaridad();
        $nivelEscolaridad = ($temaNivelEscolaridad && isset($temaNivelEscolaridad->parametros) && $temaNivelEscolaridad->parametros->count() > 0)
            ? $temaNivelEscolaridad
            : (object) ['parametros' => collect()];

        $paises = Pais::all();
        $departamentos = Departamento::all();
        $municipios = collect();

        return view('complementarios.aspirantes.create', array_merge($data, [
            'documentos' => $documentos,
            'generos' => $generos,
            'caracterizaciones' => $caracterizaciones,
            'vias' => $vias,
            'letras' => $letras,
            'cardinales' => $cardinales,
            'nivelEscolaridad' => $nivelEscolaridad,
            'paises' => $paises,
            'departamentos' => $departamentos,
            'municipios' => $municipios,
        ]));
    }

    /**
     * Almacenar nuevo aspirante a un programa complementario
     *
     * Este método sigue las convenciones de Laravel Resource Controller.
     * Agrega una persona EXISTENTE como aspirante a un programa.
     * Para crear una persona nueva y agregarla como aspirante, usar storeNewAspirante().
     *
     * @param StoreAspiranteRequest $request Request validado con número de documento y observaciones
     * @param int|null $programa ID del programa complementario (puede venir como 'programa' o 'complementarioId' en la ruta)
     * @return JsonResponse Respuesta JSON con resultado de la operación
     */
    public function store(StoreAspiranteRequest $request, ?int $programa = null): JsonResponse
    {
        try {
            // Obtener ID del programa desde la ruta (puede venir como 'programa' o 'complementarioId')
            $programaId = $programa ?? $request->route('complementarioId') ?? $request->route('programa');

            if ($programaId === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID del programa no proporcionado.',
                ], 400);
            }

            // Obtener datos validados del request
            $validated = $request->validated();
            $numeroDocumento = $validated['numero_documento'];
            $observaciones = $validated['observaciones'] ?? null;

            // Ejecutar la lógica de negocio mediante el servicio
            // El servicio contiene todas las validaciones de negocio:
            // - Validación de existencia del programa
            // - Validación de existencia de la persona
            // - Validación de que la persona no esté ya inscrita
            $resultado = $this->aspiranteManagementService->agregarAspirante(
                $programaId,
                $numeroDocumento,
                $observaciones
            );

            // Extraer código de estado y retornar respuesta
            $statusCode = $resultado['status_code'] ?? 200;
            unset($resultado['status_code']);

            return response()->json($resultado, $statusCode);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en store() al agregar aspirante: ' . $e->getMessage(), [
                'programa' => $programaId ?? null,
                'numero_documento' => $request->validated()['numero_documento'] ?? null,
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => self::ERROR_MENSAJE_SERVIDOR,
            ], 500);
        }
    }

    /**
     * Crear nueva persona y agregarla como aspirante a un programa complementario
     *
     * Crea una nueva persona en el sistema y simultáneamente la agrega como aspirante.
     *
     * @param CreateAspiranteRequest $request Request validado con todos los datos de la persona
     * @param int $programa ID del programa complementario
     * @return \Illuminate\Http\RedirectResponse|JsonResponse Respuesta según el tipo de request
     */
    public function storeNewAspirante(CreateAspiranteRequest $request, int $programa)
    {
        $resultado = $this->procesarCreacionAspirante($request, $programa);
        return $this->formatearRespuesta($request, $resultado, $programa);
    }

    /**
     * Procesar la creación de un nuevo aspirante
     *
     * @param CreateAspiranteRequest $request
     * @param int $programa
     * @return array Resultado de la operación
     */
    private function procesarCreacionAspirante(CreateAspiranteRequest $request, int $programa): array
    {
        try {
            $validated = $request->validated();

            // Validar que el programa exista
            $errorValidacion = $this->validarProgramaYPersona($programa, $validated['numero_documento']);
            if ($errorValidacion !== null) {
                return $errorValidacion;
            }

            // Preparar y crear persona
            $persona = $this->crearPersonaDesdeValidacion($validated);

            // Agregar como aspirante al programa
            $observaciones = $validated['observaciones'] ?? 'Creado desde gestión de aspirantes';
            $resultado = $this->aspiranteManagementService->agregarAspirante(
                $programa,
                $persona->numero_documento,
                $observaciones
            );

            $statusCode = $resultado['status_code'] ?? 200;
            unset($resultado['status_code']);
            $resultado['status_code'] = $statusCode;

            return $resultado;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en storeNewAspirante() al crear aspirante: ' . $e->getMessage(), [
                'programa' => $programa,
                'numero_documento' => $request->validated()['numero_documento'] ?? null,
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => self::ERROR_MENSAJE_SERVIDOR,
                'status_code' => 500
            ];
        }
    }

    /**
     * Validar programa y verificar que la persona no exista
     *
     * @param int $programa
     * @param string $numeroDocumento
     */
    private function validarProgramaYPersona(int $programa, string $numeroDocumento): ?array
    {
        $programaModel = $this->programaRepository->findWithRelations($programa);
        if (!$programaModel) {
            return [
                'success' => false,
                'message' => 'Programa no encontrado.',
                'status_code' => 200
            ];
        }

        $personaExistente = $this->personaService->buscarPorDocumento($numeroDocumento);
        if ($personaExistente) {
            return [
                'success' => false,
                'message' => 'Ya existe una persona con este número de documento. Use "Agregar Aspirante" en lugar de "Crear Nuevo".',
                'status_code' => 200
            ];
        }

        return null;
    }

    /**
     * Crear persona desde datos validados
     *
     * @param array $validated
     * @return \App\Models\Persona
     */
    private function crearPersonaDesdeValidacion(array $validated): \App\Models\Persona
    {
        $tipoDocumentoId = $validated['tipo_documento_id'] ?? $validated['tipo_documento'] ?? null;

        $datosPersona = [
            'tipo_documento' => $tipoDocumentoId,
            'numero_documento' => $validated['numero_documento'],
            'primer_nombre' => $validated['primer_nombre'],
            'segundo_nombre' => $validated['segundo_nombre'] ?? null,
            'primer_apellido' => $validated['primer_apellido'],
            'segundo_apellido' => $validated['segundo_apellido'] ?? null,
            'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
            'genero' => $validated['genero_id'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'celular' => $validated['celular'] ?? null,
            'email' => $validated['email'] ?? null,
            'pais_id' => $validated['pais_id'] ?? null,
            'departamento_id' => $validated['departamento_id'] ?? null,
            'municipio_id' => $validated['municipio_id'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
            'caracterizacion_ids' => $validated['caracterizaciones'] ?? [], // PersonaService espera 'caracterizacion_ids'
            'nivel_escolaridad_id' => $validated['nivel_escolaridad_id'] ?? null,
        ];

        // Convertir parametro_id a parametros_temas.id para tipo_documento, genero y nivel_escolaridad_id
        $datosPersona = $this->convertirParametrosAParametrosTemas($datosPersona);

        return $this->personaService->crear($datosPersona);
    }

    /**
     * Convertir parametro_id a parametros_temas.id para tipo_documento, genero y nivel_escolaridad_id
     */
    private function convertirParametrosAParametrosTemas(array $data): array
    {
        // Convertir tipo_documento (parametro_id) a parametros_temas.id
        if (isset($data['tipo_documento'])) {
            $parametroTema = \App\Models\ParametroTema::where('tema_id', 2) // TIPO DE DOCUMENTO
                ->where('parametro_id', $data['tipo_documento'])
                ->first();

            if ($parametroTema) {
                $data['tipo_documento'] = $parametroTema->id;
            }
        }

        // Convertir genero (parametro_id) a parametros_temas.id
        if (isset($data['genero'])) {
            $parametroTema = \App\Models\ParametroTema::where('tema_id', 3) // GENERO
                ->where('parametro_id', $data['genero'])
                ->first();

            if ($parametroTema) {
                $data['genero'] = $parametroTema->id;
            }
        }

        // Convertir nivel_escolaridad_id (parametro_id) a parametros_temas.id
        if (isset($data['nivel_escolaridad_id'])) {
            $parametroTema = \App\Models\ParametroTema::where('tema_id', 23) // NIVEL-ESCOLARIDAD
                ->where('parametro_id', $data['nivel_escolaridad_id'])
                ->first();

            if ($parametroTema) {
                $data['nivel_escolaridad_id'] = $parametroTema->id;
            }
        }

        return $data;
    }

    /**
     * Formatear respuesta según el tipo de request
     *
     * @param \Illuminate\Http\Request $request
     * @param array $resultado
     * @param int $programa
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    private function formatearRespuesta(\Illuminate\Http\Request $request, array $resultado, int $programa)
    {
        $statusCode = $resultado['status_code'] ?? 200;
        $success = $resultado['success'] ?? false;
        $message = $resultado['message'] ?? '';
        unset($resultado['status_code']);

        // Si es una petición AJAX, retornar JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($resultado, $statusCode);
        }

        // Si es una petición normal, redirigir
        if ($success) {
            return redirect()
                ->route('aspirantes.programa', ['programa' => $programa])
                ->with('success', $message ?: 'Aspirante creado exitosamente.');
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', $message ?: 'Error al crear el aspirante.');
    }

    /**
     * Actualizar aspirante de un programa complementario
     *
     * Este método sigue las convenciones de Laravel Resource Controller.
     * Permite actualizar el estado y observaciones de un aspirante.
     *
     * @param UpdateAspiranteRequest $request Request validado con estado y observaciones
     * @param int $programa ID del programa complementario
     * @param int $aspirante ID del aspirante a actualizar
     * @return JsonResponse Respuesta JSON con resultado de la operación
     */
    public function update(UpdateAspiranteRequest $request, int $programa, int $aspirante): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Validar que el aspirante exista y pertenezca al programa
            $aspiranteModel = $this->aspiranteRepository->findByPrograma($programa)
                ->where('id', $aspirante)
                ->first();

            if (!$aspiranteModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aspirante no encontrado en este programa.',
                ], 200);
            }

            // Actualizar solo los campos proporcionados
            $updateData = [];
            if (isset($validated['estado'])) {
                $updateData['estado'] = $validated['estado'];
            }
            if (isset($validated['observaciones'])) {
                $updateData['observaciones'] = $validated['observaciones'];
            }

            if (!empty($updateData)) {
                $this->aspiranteRepository->update($aspiranteModel, $updateData);

                \Illuminate\Support\Facades\Log::info('Aspirante actualizado exitosamente', [
                    'aspirante_id' => $aspirante,
                    'complementario_id' => $programa,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'updated_fields' => array_keys($updateData)
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Aspirante actualizado exitosamente.',
            ], 200);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en update() al actualizar aspirante: ' . $e->getMessage(), [
                'programa' => $programa,
                'aspirante' => $aspirante,
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => self::ERROR_MENSAJE_SERVIDOR,
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de exclusión para modal
     */
    public function getEstadisticasExclusion(int $complementarioId): JsonResponse
    {
        try {
            $estadisticas = $this->aspiranteRepository->getEstadisticasExclusion($complementarioId);

            return response()->json([
                'success' => true,
                'estadisticas' => $estadisticas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de exclusión: ' . $e->getMessage()
            ], 500);
        }
    }
}
