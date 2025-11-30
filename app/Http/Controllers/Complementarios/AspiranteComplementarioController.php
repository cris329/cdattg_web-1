<?php

namespace App\Http\Controllers\Complementarios;

use App\Http\Controllers\Controller;
use App\Http\Requests\Complementarios\AspiranteRequest;
use App\Services\Complementarios\AspiranteManagementService;
use App\Services\Complementarios\AspiranteExportService;
use App\Services\Complementarios\AspiranteDocumentoService;
use App\Services\Complementarios\ComplementarioService;
use App\Services\PersonaService;
use App\Repositories\Complementarios\AspiranteComplementarioRepository;
use App\Repositories\TemaRepository;
use App\Models\Pais;
use App\Models\Departamento;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AspiranteComplementarioController extends Controller
{
    public function __construct(
        private readonly AspiranteManagementService $aspiranteManagementService,
        private readonly AspiranteExportService $exportService,
        private readonly AspiranteDocumentoService $documentoService,
        private readonly PersonaService $personaService,
        private readonly AspiranteComplementarioRepository $aspiranteRepository,
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
     * Mostrar gestión de aspirantes (Admin)
     */
    public function gestionAspirantes(): View
    {
        return $this->index();
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
        $data = $this->aspiranteManagementService->obtenerAspirantesPorProgramaId($programa);

        return view('complementarios.aspirantes.programa', $data);
    }

    /**
     * Agregar aspirante existente a un programa complementario
     */
    public function agregarAspirante(AspiranteRequest $request, int $complementarioId): JsonResponse
    {
        $resultado = $this->aspiranteManagementService->agregarAspirante(
            $complementarioId,
            $request->validated()['numero_documento']
        );

        $statusCode = $resultado['status_code'] ?? 200;
        unset($resultado['status_code']);

        return response()->json($resultado, $statusCode);
    }

    /**
     * Rechazar aspirante de un programa complementario (cambiar estado a rechazado)
     */
    public function eliminarAspirante(int $complementarioId, int $aspiranteId): JsonResponse
    {
        $resultado = $this->aspiranteManagementService->rechazarAspirante($complementarioId, $aspiranteId);

        $statusCode = $resultado['status_code'] ?? 200;
        unset($resultado['status_code']);

        return response()->json($resultado, $statusCode);
    }

    /**
     * Exportar aspirantes a Excel
     */
    public function exportarAspirantesExcel(int $complementarioId): StreamedResponse
    {
        try {
            return $this->exportService->exportarAspirantesExcel($complementarioId);
        } catch (\Exception $e) {
            // En caso de error, redirigir con mensaje
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
    public function buscarPersona(Request $request): JsonResponse
    {
        $request->validate([
            'numero_documento' => 'required|string|max:20'
        ]);

        $persona = $this->personaService->buscarPorDocumento(trim($request->numero_documento));

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
                'tipo_documento' => $persona->tipoDocumento ? $persona->tipoDocumento->name : null,
                'numero_documento' => $persona->numero_documento,
                'primer_nombre' => $persona->primer_nombre,
                'segundo_nombre' => $persona->segundo_nombre,
                'primer_apellido' => $persona->primer_apellido,
                'segundo_apellido' => $persona->segundo_apellido,
                'fecha_nacimiento' => $persona->fecha_nacimiento,
                'genero_id' => $persona->genero,
                'genero' => $persona->tipoGenero ? $persona->tipoGenero->name : null,
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
        $generos = $temaGenero && $temaGenero->parametros->count() > 0
            ? $temaGenero->parametros()->where('parametros_temas.status', 1)->orderBy('parametros.name')->get(['parametros.id', 'parametros.name'])
            : $generosFallback;
        
        $temaCaracterizacion = $this->temaRepository->obtenerCaracterizacionesComplementarias();
        $caracterizaciones = $temaCaracterizacion && $temaCaracterizacion->parametros->count() > 0
            ? $temaCaracterizacion->parametros()->where('parametros_temas.status', 1)->orderBy('parametros.name')->get(['parametros.id', 'parametros.name'])
            : collect();
        
        $temaVia = $this->temaRepository->obtenerVias();
        $vias = $temaVia && $temaVia->parametros->count() > 0
            ? $temaVia->parametros()->where('parametros_temas.status', 1)->orderBy('parametros.name')->get(['parametros.id', 'parametros.name'])
            : collect();
        
        $temaLetra = $this->temaRepository->obtenerLetras();
        $letras = $temaLetra && $temaLetra->parametros->count() > 0
            ? $temaLetra->parametros()->where('parametros_temas.status', 1)->orderBy('parametros.name')->get(['parametros.id', 'parametros.name'])
            : collect();
        
        $temaCardinal = $this->temaRepository->obtenerCardinales();
        $cardinales = $temaCardinal && $temaCardinal->parametros->count() > 0
            ? $temaCardinal->parametros()->where('parametros_temas.status', 1)->orderBy('parametros.name')->get(['parametros.id', 'parametros.name'])
            : collect();
        
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
            'paises' => $paises,
            'departamentos' => $departamentos,
            'municipios' => $municipios,
        ]));
    }

    /**
     * Almacenar nuevo aspirante
     */
    public function store(AspiranteRequest $request, int $programa): JsonResponse
    {
        $resultado = $this->aspiranteManagementService->agregarAspirante(
            $programa,
            $request->validated()['numero_documento']
        );

        $statusCode = $resultado['status_code'] ?? 200;
        unset($resultado['status_code']);

        return response()->json($resultado, $statusCode);
    }

    /**
     * Obtener estadísticas de exclusión para modal
     */
    public function getEstadisticasExclusion(int $complementarioId): JsonResponse
    {
        $estadisticas = $this->aspiranteRepository->getEstadisticasExclusion($complementarioId);

        return response()->json($estadisticas);
    }
}
