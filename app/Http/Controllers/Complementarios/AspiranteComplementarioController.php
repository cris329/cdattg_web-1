<?php

namespace App\Http\Controllers\Complementarios;

use App\Http\Controllers\Controller;
use App\Http\Requests\Complementarios\AspiranteRequest;
use App\Services\AspiranteManagementService;
use App\Services\AspiranteExportService;
use App\Services\AspiranteDocumentoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AspiranteComplementarioController extends Controller
{
    public function __construct(
        private readonly AspiranteManagementService $aspiranteManagementService,
        private readonly AspiranteExportService $exportService,
        private readonly AspiranteDocumentoService $documentoService
    ) {}

    /**
     * Mostrar gestión de aspirantes (Admin)
     */
    public function gestionAspirantes(): View
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

        return view('complementarios.ver_aspirantes', $data);
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
}
