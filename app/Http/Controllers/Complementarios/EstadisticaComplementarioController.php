<?php

namespace App\Http\Controllers\Complementarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Services\Complementarios\EstadisticaComplementarioService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class EstadisticaComplementarioController extends Controller
{
    protected $estadisticaService;

    public function __construct(EstadisticaComplementarioService $estadisticaService)
    {
        $this->estadisticaService = $estadisticaService;
    }

    /**
     * Mostrar dashboard de estadísticas
     */
    public function estadisticas()
    {
        $departamentos = Departamento::select('id', 'departamento')->get();
        $municipios = Municipio::select('id', 'municipio')->get();

        // Obtener datos reales para las estadísticas
        $estadisticas = $this->estadisticaService->obtenerEstadisticasReales();

        return view('complementarios.estadisticas', compact('departamentos', 'municipios', 'estadisticas'));
    }

    /**
     * API para obtener estadísticas con filtros
     */
    public function apiEstadisticas(Request $request)
    {
        $filtros = $request->only(['fecha_inicio', 'fecha_fin', 'departamento_id', 'municipio_id', 'programa_id']);

        // Si hay filtros, usar el método filtrado; si no, usar el método general
        if (!empty(array_filter($filtros))) {
            $estadisticas = $this->estadisticaService->obtenerEstadisticasFiltradas($filtros);
        } else {
            $estadisticas = $this->estadisticaService->obtenerEstadisticasReales();
        }

        return response()->json($estadisticas);
    }

    /**
     * Exportar programas con mayor demanda a Excel
     */
    public function exportarProgramasDemandaExcel(): StreamedResponse
    {
        try {
            return $this->estadisticaService->exportarProgramasDemandaExcel();
        } catch (\Exception $e) {
            Log::error('Error en controlador al exportar programas con mayor demanda', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            abort(500, 'Error al generar el archivo Excel. Por favor intente nuevamente.');
        }
    }
}

