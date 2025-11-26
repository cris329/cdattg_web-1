<?php

namespace App\Http\Controllers\Complementarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ComplementarioOfertado;
use App\Models\AspiranteComplementario;
use App\Models\Persona;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use setasign\Fpdi\Fpdi;
use App\Services\AspiranteDocumentoService;
use App\Services\AspiranteComplementarioService;
use App\Services\ComplementarioService;
use App\Repositories\TemaRepository;
use App\Models\Pais;
use App\Models\Departamento;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AspiranteComplementarioController extends Controller
{
    private const COLOR_NEGRO_RGB = '000000';

    protected $documentoService;
    protected $complementarioService;
    protected $temaRepository;
    protected $complementarioServiceHelper;

    public function __construct(
        AspiranteDocumentoService $documentoService,
        TemaRepository $temaRepository,
        ComplementarioService $complementarioServiceHelper
    ) {
        $this->documentoService = $documentoService;
        $this->complementarioService = new AspiranteComplementarioService($documentoService);
        $this->temaRepository = $temaRepository;
        $this->complementarioServiceHelper = $complementarioServiceHelper;
    }

    /**
     * Mostrar lista de programas complementarios (Index de aspirantes)
     */
    public function index()
    {
        $programas = ComplementarioOfertado::with(['modalidad.parametro', 'jornada', 'diasFormacion'])->get();

        // Add aspirantes count for each program
        $programas->each(function ($programa) {
            $programa->aspirantes_count = AspiranteComplementario::where('complementario_id', $programa->id)->count();
        });

        return view('complementarios.aspirantes.index', compact('programas'));
    }

    /**
     * Mostrar aspirantes de un programa específico
     */
    public function programa(ComplementarioOfertado $programa)
    {
        // Get aspirantes for this program
        $aspirantes = AspiranteComplementario::with(['persona', 'complementario'])
            ->where('complementario_id', $programa->id)
            ->get();

        // Check for existing validation progress for this program
        $existingProgress = \App\Models\SofiaValidationProgress::where('complementario_id', $programa->id)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        return view('complementarios.aspirantes.programa', compact('programa', 'aspirantes', 'existingProgress'));
    }

    /**
     * Buscar persona por número de documento
     */
    public function buscarPersona(Request $request)
    {
        $request->validate([
            'numero_documento' => 'required|string|max:191',
        ]);

        $persona = Persona::where('numero_documento', $request->numero_documento)->first();

        if (!$persona) {
            return response()->json([
                'success' => false,
                'found' => false,
                'message' => 'No se encontró ninguna persona con este número de documento.',
            ]);
        }

        // Verificar si ya está inscrita en algún programa (opcional, para mostrar info)
        $aspirantes = AspiranteComplementario::where('persona_id', $persona->id)->count();

        return response()->json([
            'success' => true,
            'found' => true,
            'persona' => [
                'id' => $persona->id,
                'numero_documento' => $persona->numero_documento,
                'nombre_completo' => trim(($persona->primer_nombre ?? '') . ' ' .
                                         ($persona->segundo_nombre ?? '') . ' ' .
                                         ($persona->primer_apellido ?? '') . ' ' .
                                         ($persona->segundo_apellido ?? '')),
                'email' => $persona->email ?? 'No registrado',
                'telefono' => $persona->telefono ?? $persona->celular ?? 'No registrado',
                'aspirantes_count' => $aspirantes,
            ],
        ]);
    }

    /**
     * Mostrar formulario para crear nuevo aspirante
     */
    public function create(Request $request, ComplementarioOfertado $programa)
    {
        // Obtener datos para el formulario
        $documentos = $this->buildTemaPayload(
            $this->temaRepository->obtenerTiposDocumento(),
            $this->complementarioServiceHelper->getTiposDocumento()
        );
        $generos = $this->buildTemaPayload(
            $this->temaRepository->obtenerGeneros(),
            $this->complementarioServiceHelper->getGeneros()
        );
        $caracterizaciones = $this->buildTemaPayload(
            $this->temaRepository->obtenerCaracterizacionesComplementarias()
        );
        $vias = $this->buildTemaPayload($this->temaRepository->obtenerVias());
        $letras = $this->buildTemaPayload($this->temaRepository->obtenerLetras());
        $cardinales = $this->buildTemaPayload($this->temaRepository->obtenerCardinales());

        $paises = Pais::where('status', 1)->get();
        $departamentos = Departamento::where('status', 1)->get();
        $municipios = collect();

        // Prellenar datos si se viene desde búsqueda sin encontrar persona
        if ($request->has('numero_documento')) {
            // Agregar a old() para que el formulario lo prellene
            $request->flash();
        }

        return view('complementarios.aspirantes.create', compact(
            'programa',
            'documentos',
            'generos',
            'caracterizaciones',
            'vias',
            'letras',
            'cardinales',
            'paises',
            'departamentos',
            'municipios'
        ));
    }

    /**
     * Almacenar nuevo aspirante
     */
    public function store(Request $request, ComplementarioOfertado $programa)
    {
        $validated = $request->validate([
            'tipo_documento' => 'required|integer',
            'numero_documento' => 'required|string|max:191',
            'primer_nombre' => 'required|string|max:191',
            'segundo_nombre' => 'nullable|string|max:191',
            'primer_apellido' => 'required|string|max:191',
            'segundo_apellido' => 'nullable|string|max:191',
            'fecha_nacimiento' => 'required|date',
            'genero' => 'required|integer',
            'telefono' => 'nullable|string|max:191',
            'celular' => 'nullable|string|max:191',
            'email' => 'nullable|email|max:191',
            'pais_id' => 'required|integer',
            'departamento_id' => 'required|integer',
            'municipio_id' => 'required|integer',
            'direccion' => 'nullable|string|max:191',
            'observaciones' => 'nullable|string',
            'caracterizacion_ids' => 'nullable|array',
            'caracterizacion_ids.*' => 'integer|exists:parametros,id',
        ]);

        try {
            // Verificar si la persona ya existe
            $personaExistente = Persona::where('numero_documento', $validated['numero_documento'])
                ->orWhere(function ($query) use ($validated) {
                    if (!empty($validated['email'])) {
                        $query->where('email', $validated['email']);
                    }
                })
                ->first();

            if ($personaExistente) {
                // Si existe, verificar si ya está inscrita en este programa
                $aspiranteExistente = AspiranteComplementario::where('persona_id', $personaExistente->id)
                    ->where('complementario_id', $programa->id)
                    ->first();

                if ($aspiranteExistente) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'La persona ya está inscrita en este programa complementario.');
                }

                // Si existe pero no está inscrita, actualizar datos y crear aspirante
                $personaExistente->update($validated);
                $persona = $personaExistente->fresh();
            } else {
                // Crear nueva persona
                $persona = Persona::create($validated + [
                    'user_create_id' => auth()->id() ?? 1,
                    'user_edit_id' => auth()->id() ?? 1,
                ]);
            }

            // Crear o actualizar usuario con rol ASPIRANTE
            $this->crearOActualizarUsuarioAspirante($persona);

            // Crear aspirante
            AspiranteComplementario::create([
                'persona_id' => $persona->id,
                'complementario_id' => $programa->id,
                'estado' => 1, // En proceso
                'observaciones' => $validated['observaciones'] ?? 'Creado desde formulario de aspirantes',
            ]);

            return redirect()
                ->route('aspirantes.programa', ['programa' => $programa->id])
                ->with('success', 'Aspirante creado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear aspirante', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'programa_id' => $programa->id,
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el aspirante. Por favor, inténtalo nuevamente.');
        }
    }

    /**
     * Construir payload de tema para el formulario
     */
    private function buildTemaPayload($tema = null, $parametros = null)
    {
        if ($tema && $tema->parametros && $tema->parametros->count() > 0) {
            return $tema;
        }

        if ($parametros && $parametros->count() > 0) {
            return (object) [
                'id' => null,
                'parametros' => $parametros,
            ];
        }

        return (object) [
            'id' => null,
            'parametros' => collect(),
        ];
    }

    /**
     * Crear o actualizar usuario para aspirante con rol ASPIRANTE
     */
    private function crearOActualizarUsuarioAspirante(Persona $persona)
    {
        $user = null;

        // Validar datos requeridos
        if (empty($persona->email) || empty($persona->numero_documento)) {
            if (empty($persona->email)) {
                Log::warning('No se puede crear usuario para aspirante sin email', [
                    'persona_id' => $persona->id,
                    'numero_documento' => $persona->numero_documento
                ]);
            }
            if (empty($persona->numero_documento)) {
                Log::warning('No se puede crear usuario para aspirante sin número de documento', [
                    'persona_id' => $persona->id
                ]);
            }
            return $user;
        }

        // Si ya tiene usuario, solo asegurar que tenga el rol ASPIRANTE
        if ($persona->user) {
            $user = $persona->user;
            Role::firstOrCreate(['name' => 'ASPIRANTE']);

            if (!$user->hasRole('ASPIRANTE')) {
                $user->assignRole('ASPIRANTE');
                Log::info('Rol ASPIRANTE asignado a usuario existente', [
                    'user_id' => $user->id,
                    'persona_id' => $persona->id
                ]);
            }
        } else {
            // Crear nuevo usuario
            try {
                $existingUser = User::where('email', $persona->email)->first();
                if ($existingUser) {
                    Log::warning('Email ya está en uso por otro usuario', [
                        'email' => $persona->email,
                        'persona_id' => $persona->id,
                        'existing_user_id' => $existingUser->id
                    ]);
                } else {
                    $user = User::create([
                        'email' => strtolower($persona->email),
                        'password' => Hash::make($persona->numero_documento),
                        'persona_id' => $persona->id,
                        'status' => 1,
                    ]);

                    Role::firstOrCreate(['name' => 'ASPIRANTE']);
                    $user->assignRole('ASPIRANTE');
                    $user->sendEmailVerificationNotification();

                    Log::info('Usuario creado para aspirante con rol ASPIRANTE', [
                        'user_id' => $user->id,
                        'persona_id' => $persona->id,
                        'email' => $user->email
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error al crear usuario para aspirante', [
                    'persona_id' => $persona->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $user = null;
            }
        }

        return $user;
    }

    /**
     * Agregar aspirante existente a un programa complementario
     */
    public function agregarAspirante(Request $request, $complementarioId)
    {
        $request->validate([
            'numero_documento' => 'required|string|max:191',
        ]);

        try {
            // Verificar que el programa existe
            ComplementarioOfertado::findOrFail($complementarioId);

            // Buscar persona por número de documento
            $persona = Persona::where('numero_documento', $request->numero_documento)->first();

            $response = null;

            // Validar que la persona existe
            if (!$persona) {
                $response = $this->createErrorResponse(
                    'No se encontró ninguna persona registrada con el número de documento "' .
                        $request->numero_documento . '".'
                );
            }

            // Verificar si ya está inscrita en este programa
            if ($response === null) {
                $aspiranteExistente = AspiranteComplementario::where('persona_id', $persona->id)
                    ->where('complementario_id', $complementarioId)
                    ->first();

                if ($aspiranteExistente) {
                    $response = $this->createErrorResponse(
                        'La persona con documento "' . $request->numero_documento .
                            '" ya se encuentra inscrita en este programa complementario.'
                    );
                }
            }

            // Si no hay errores, procesar la creación del aspirante
            if ($response === null) {
                // Crear nuevo aspirante - ahora permite múltiples programas por persona
                AspiranteComplementario::create([
                    'persona_id' => $persona->id,
                    'complementario_id' => $complementarioId,
                    'estado' => 1, // Estado "En proceso"
                    'observaciones' => 'Agregado manualmente desde gestión de aspirantes'
                ]);

                // Crear o actualizar usuario con rol ASPIRANTE
                $this->crearOActualizarUsuarioAspirante($persona);

                $response = $this->createSuccessResponse(
                    'Aspirante agregado exitosamente. ' . $persona->primer_nombre . ' ' .
                        $persona->primer_apellido . ' ha sido inscrito en el programa.'
                );
            }

            return $response;
        } catch (\Exception $e) {
            return $this->handleAspiranteException($e, $complementarioId, $request->numero_documento);
        }
    }

    /**
     * Crear respuesta JSON de error
     */
    private function createErrorResponse($message)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ]);
    }

    /**
     * Crear respuesta JSON de éxito
     */
    private function createSuccessResponse($message)
    {
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Manejar excepciones en operaciones de aspirantes
     */
    private function handleAspiranteException(\Exception $e, $complementarioId, $numeroDocumento)
    {
        Log::error('Error agregando aspirante: ' . $e->getMessage(), [
            'complementario_id' => $complementarioId,
            'numero_documento' => $numeroDocumento,
            'exception' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor. Por favor intente nuevamente.'
        ], 500);
    }

    /**
     * Rechazar aspirante de un programa complementario (cambiar estado a rechazado)
     */
    public function eliminarAspirante($complementarioId, $aspiranteId)
    {
        // Verificar permisos del usuario (solo administradores pueden rechazar)
        if (!auth()->user()->can('ELIMINAR ASPIRANTE COMPLEMENTARIO')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para rechazar aspirantes.'
            ], 403);
        }

        try {
            // Verificar que el programa existe
            ComplementarioOfertado::findOrFail($complementarioId);

            // Verificar que el aspirante existe y pertenece al programa
            $aspirante = AspiranteComplementario::where('id', $aspiranteId)
                ->where('complementario_id', $complementarioId)
                ->with('persona')
                ->firstOrFail();

            // Guardar información del aspirante para el mensaje
            $personaNombre = $aspirante->persona->primer_nombre . ' ' . $aspirante->persona->primer_apellido;
            $numeroDocumento = $aspirante->persona->numero_documento;

            // Cambiar el estado a rechazado (4) en lugar de eliminar
            $aspirante->estado = 4;
            $aspirante->save();

            Log::info('Aspirante rechazado exitosamente', [
                'aspirante_id' => $aspiranteId,
                'complementario_id' => $complementarioId,
                'persona_id' => $aspirante->persona_id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Aspirante rechazado exitosamente. ' .
                    $personaNombre . ' (' . $numeroDocumento . ') ha sido marcado como rechazado en el programa.'
            ]);
        } catch (\Exception $e) {
            $statusCode = 500;
            $message = 'Error interno del servidor. Por favor intente nuevamente.';

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $statusCode = 404;
                $message = 'Aspirante o programa no encontrado.';
            } else {
                Log::error('Error rechazando aspirante: ' . $e->getMessage(), [
                    'complementario_id' => $complementarioId,
                    'aspirante_id' => $aspiranteId,
                    'user_id' => auth()->id(),
                    'exception' => $e->getTraceAsString()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $message
            ], $statusCode);
        }
    }

    /**
     * Convertir tipo de documento a iniciales (CC, TI, etc.)
     */
    private function convertirTipoDocumentoAIniciales($tipoDocumento)
    {
        // Limpiar el texto y quitar acentos
        $tipoDocumento = $this->limpiarTexto($tipoDocumento);
        
        // Mapeo de tipos de documento a sus iniciales
        $mapeo = [
            'cedula de ciudadania' => 'CC',
            'cedula de extranjeria' => 'CE',
            'tarjeta de identidad' => 'TI',
            'pasaporte' => 'PA',
            'registro civil' => 'RC',
            'cedula' => 'CC',
            'extranjeria' => 'CE',
            'tarjeta identidad' => 'TI',
        ];

        // Buscar coincidencia exacta primero
        $tipoDocumentoLower = strtolower($tipoDocumento);
        if (isset($mapeo[$tipoDocumentoLower])) {
            return $mapeo[$tipoDocumentoLower];
        }

        // Buscar coincidencia parcial
        foreach ($mapeo as $nombre => $iniciales) {
            if (strpos($tipoDocumentoLower, $nombre) !== false) {
                return $iniciales;
            }
        }

        // Si no se encuentra coincidencia, devolver las primeras 2 letras en mayúsculas
        return strtoupper(substr($tipoDocumento, 0, 2));
    }

    /**
     * Limpiar texto quitando acentos y caracteres especiales
     */
    private function limpiarTexto($texto)
    {
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);
        $texto = preg_replace('/[^a-zA-Z0-9\s]/', '', $texto);
        return trim($texto);
    }

    /**
     * Obtener estadísticas de exclusión para modal
     */
    public function getEstadisticasExclusion($complementarioId)
    {
        try {
            $programa = ComplementarioOfertado::findOrFail($complementarioId);
            $estadisticas = $this->complementarioService->getEstadisticasExclusion($complementarioId);
            
            return response()->json([
                'success' => true,
                'estadisticas' => $estadisticas,
                'programa_nombre' => $programa->nombre
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de exclusión: ' . $e->getMessage(), [
                'complementario_id' => $complementarioId,
                'exception' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de exclusión'
            ], 500);
        }
    }

    /**
     * Exportar aspirantes a Excel
     */
    public function exportarAspirantesExcel($complementarioId)
    {
        try {
            // Verificar que el programa existe
            $programa = ComplementarioOfertado::findOrFail($complementarioId);

            // Obtener aspirantes válidos para exportación (excluye rechazados y sin documento)
            $aspirantes = $this->complementarioService->getAspirantesParaExportacion($complementarioId);

            // Log para debugging
            Log::info('Exportando aspirantes a Excel', [
                'complementario_id' => $complementarioId,
                'total_aspirantes' => $aspirantes->count(),
                'aspirantes_con_caracterizacion' => $aspirantes->filter(function($aspirante) {
                    return $aspirante->persona->parametro_id !== null;
                })->count()
            ]);

            // Crear nueva hoja de cálculo
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Agregar título
            $sheet->setCellValue('A1', 'FORMATO PARA LA INSCRIPCIÓN DE ASPIRANTES EN SOFIA PLUS v1.0');
            $sheet->mergeCells('A1:G1');
            
            // Estilo para el título con bordes oscuros
            $titleStyle = [
                'font' => [
                    'bold' => false,
                    'size' => 14,
                    'color' => ['rgb' => self::COLOR_NEGRO_RGB],
                    'name' => 'Calibri',
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'C4D79B'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                        'color' => ['rgb' => self::COLOR_NEGRO_RGB],
                    ],
                ],
            ];
            $sheet->getStyle('A1:G1')->applyFromArray($titleStyle);

            // Establecer encabezados
            $sheet->setCellValue('A2', 'Resultado del Registro (Reservado para el sistema)');
            $sheet->setCellValue('B2', 'Tipo de Identificación');
            $sheet->setCellValue('C2', 'Número de Identificación');
            $sheet->setCellValue('D2', 'Código de la ficha');
            $sheet->setCellValue('E2', 'Tipo Población Aspirante');
            $sheet->setCellValue('F2', '');
            $sheet->setCellValue('G2', 'Codigo Empresa (Solo si la ficha es cerrada)');

            // Estilo para encabezados con bordes oscuros, centrado vertical y horizontal, tamaño 8 y ajuste de texto
            $headerStyle = [
                'font' => [
                    'bold' => false,
                    'color' => ['rgb' => self::COLOR_NEGRO_RGB],
                    'name' => 'Calibri',
                    'size' => 8,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                        'color' => ['rgb' => self::COLOR_NEGRO_RGB],
                    ],
                ],
            ];
            $sheet->getStyle('A2:G2')->applyFromArray($headerStyle);

            // Llenar datos
            $row = 3;
            foreach ($aspirantes as $aspirante) {
                $tipoDocumento = $aspirante->persona->tipoDocumento ? $aspirante->persona->tipoDocumento->name : 'N/A';
                $numeroDocumento = $aspirante->persona->numero_documento;
                
                // Obtener caracterización desde parametroCaracterizacion (relación con parametro_id)
                $caracterizacionParametro = $aspirante->persona->parametroCaracterizacion;
                
                // Log detallado para debugging
                Log::info('Procesando aspirante', [
                    'aspirante_id' => $aspirante->id,
                    'persona_id' => $aspirante->persona->id,
                    'numero_documento' => $numeroDocumento,
                    'parametro_id' => $aspirante->persona->parametro_id,
                    'caracterizacion_nombre' => $caracterizacionParametro ? $caracterizacionParametro->name : null
                ]);
                
                // Obtener la caracterización desde parametroCaracterizacion
                if ($caracterizacionParametro) {
                    $caracterizacion = $caracterizacionParametro->name;
                } else {
                    $caracterizacion = 'Sin caracterización';
                }

                // Convertir tipo de documento a iniciales (CC, TI, etc.)
                $tipoIdentificacion = $this->convertirTipoDocumentoAIniciales($tipoDocumento);

                // Solo llenar los campos que se quieren capturar, los demás quedan vacíos
                $sheet->setCellValue('A' . $row, ''); // Resultado del Registro (vacío)
                $sheet->setCellValue('B' . $row, $tipoIdentificacion); // Tipo de Identificación (iniciales)
                $sheet->setCellValue('C' . $row, $numeroDocumento); // Número de Identificación
                $sheet->setCellValue('D' . $row, ''); // Código de la ficha (vacío)
                $sheet->setCellValue('E' . $row, $caracterizacion); // Tipo Población Aspirante
                $sheet->setCellValue('F' . $row, ''); // Campo vacío
                $sheet->setCellValue('G' . $row, ''); // Código Empresa (vacío)

                $row++;
            }

            // Configurar alturas de filas
            $sheet->getRowDimension(1)->setRowHeight(15); // Título con altura normal como filas de datos
            $sheet->getRowDimension(2)->setRowHeight(45); // Encabezados más altos con texto centrado verticalmente
            // Las filas de datos mantienen la altura normal por defecto (~15px)

            
            $calibriStyle = [
                'font' => [
                    'name' => 'Calibri',
                    'size' => 11,
                ],
            ];
            $sheet->getStyle('A1:G' . ($row - 1))->applyFromArray($calibriStyle);

            // Aplicar tamaño de letra 8 y ajuste de texto para filas 2 en adelante
            $dataStyle = [
                'font' => [
                    'size' => 8,
                ],
                'alignment' => [
                    'wrapText' => true,
                ],
            ];
            $sheet->getStyle('A2:G' . ($row - 1))->applyFromArray($dataStyle);

            // Configurar anchos de columna específicos
            $sheet->getColumnDimension('A')->setWidth(20); // Columna A - ancho base (100%)
            $sheet->getColumnDimension('B')->setWidth(10); // Columna B - 50% menos ancha que A (50% de 20 = 10)
            $sheet->getColumnDimension('C')->setWidth(10); // Columna C - 50% menos ancha que A (50% de 20 = 10)
            $sheet->getColumnDimension('D')->setWidth(10); // Columna D - 50% menos ancha que A (50% de 20 = 10)
            $sheet->getColumnDimension('E')->setWidth(25); // Columna E - 25% más ancha que A (125% de 20 = 25)
            $sheet->getColumnDimension('F')->setWidth(40); // Columna F - 100% más ancha que A (200% de 20 = 40)
            $sheet->getColumnDimension('G')->setWidth(10); // Columna G - 50% menos ancha que A (50% de 20 = 10)

            // Crear nombre del archivo
            $fileName = 'formato_inscripcion_sofia_plus_' . str_replace(' ', '_', $programa->nombre) . '_' .
                now()->format('Y-m-d_H-i-s') . '.xlsx';

            // Crear respuesta de descarga
            $response = new StreamedResponse(function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            });

            $response->headers->set(
                'Content-Type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );
            $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
            $response->headers->set('Cache-Control', 'max-age=0');

            return $response;
        } catch (\Exception $e) {
            Log::error('Error exportando aspirantes a Excel: ' . $e->getMessage(), [
                'complementario_id' => $complementarioId,
                'user_id' => auth()->id(),
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
    public function descargarCedulas($complementarioId)
    {
        try {
            // Verificar que el programa existe
            $programa = ComplementarioOfertado::findOrFail($complementarioId);

            // Obtener aspirantes con documentos
            $aspirantes = $this->complementarioService->getAspirantesConDocumentos($complementarioId);

            $response = null;

            if ($aspirantes->isEmpty()) {
                $response = back()->with('error', 'No hay aspirantes con documentos de identidad para descargar.');
            }

            if ($response === null) {
                $tempDir = $this->documentoService->createTempDirectory();
                $pdf = new Fpdi();

                $resultados = $this->complementarioService->procesarDescargaDocumentos($aspirantes, $pdf, $tempDir);

                if ($resultados['archivos_agregados'] === 0) {
                    $this->documentoService->limpiarArchivosTemporales($resultados['archivos_temporales']);
                    $response = back()->with(
                        'error',
                        'No se pudieron descargar los documentos. Verifique que los archivos existan en Google Drive.'
                    );
                }

                if ($response === null) {
                    $response = $this->complementarioService->generarArchivoPDF(
                        $programa,
                        $pdf,
                        $tempDir,
                        $resultados['archivos_temporales']
                    );
                }
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Error descargando cédulas: ' . $e->getMessage(), [
                'complementario_id' => $complementarioId,
                'user_id' => auth()->id(),
                'exception' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Error al generar el archivo PDF. Por favor intente nuevamente.');
        }
    }


    /**
     * Validar documentos de aspirantes en Google Drive
     */
    public function validarDocumentos($complementarioId)
    {
        try {
            // Verificar que el programa existe
            ComplementarioOfertado::findOrFail($complementarioId);

            // Obtener todos los aspirantes del programa
            $aspirantes = AspiranteComplementario::with(['persona.tipoDocumento'])
                ->where('complementario_id', $complementarioId)
                ->get();

            $response = null;

            if ($aspirantes->isEmpty()) {
                $response = response()->json([
                    'success' => false,
                    'message' => 'No hay aspirantes en este programa para validar documentos.'
                ]);
            }

            if ($response === null) {
                $files = $this->documentoService->getGoogleDriveFiles();
                $resultados = $this->complementarioService->procesarValidacionDocumentos($aspirantes, $files);

                Log::info("Validación de documentos completada", [
                    'complementario_id' => $complementarioId,
                    'total' => $resultados['total'],
                    'con_documento' => $resultados['con_documento'],
                    'sin_documento' => $resultados['sin_documento'],
                    'errores' => $resultados['errores']
                ]);

                $response = response()->json([
                    'success' => true,
                    'message' => "Validación completada. Total: {$resultados['total']}, " .
                        "Con documento: {$resultados['con_documento']}, " .
                        "Sin documento: {$resultados['sin_documento']}" .
                        ($resultados['errores'] > 0 ? ", Errores: {$resultados['errores']}" : ""),
                    'total' => $resultados['total'],
                    'con_documento' => $resultados['con_documento'],
                    'sin_documento' => $resultados['sin_documento'],
                    'errores' => $resultados['errores']
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            $statusCode = 500;
            $message = 'Error interno del servidor: ' . $e->getMessage();

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $statusCode = 404;
                $message = 'Programa no encontrado.';
            } else {
                Log::error('Error validando documentos: ' . $e->getMessage(), [
                    'complementario_id' => $complementarioId,
                    'user_id' => auth()->id(),
                    'exception' => $e->getTraceAsString()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $message
            ], $statusCode);
        }
    }

}
