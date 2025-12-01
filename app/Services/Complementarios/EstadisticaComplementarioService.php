<?php

declare(strict_types=1);

namespace App\Services\Complementarios;

use App\Models\Complementarios\AspiranteComplementario;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Repositories\Complementarios\AspiranteComplementarioRepository;
use App\Repositories\Complementarios\ComplementarioOfertadoRepository;
use App\Repositories\PersonaRepository;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EstadisticaComplementarioService
{
    public function __construct(
        private readonly AspiranteComplementarioRepository $aspiranteRepository,
        private readonly ComplementarioOfertadoRepository $programaRepository,
        private readonly PersonaRepository $personaRepository
    ) {}
    /**
     * Obtener estadísticas reales de la base de datos
     */
    public function obtenerEstadisticasReales(): array
    {
        $estadisticas = $this->aspiranteRepository->getEstadisticas();
        $totalAspirantes = $estadisticas['total'];
        $aspirantesAceptados = $estadisticas['aceptados'];

        $aspirantesPendientes = AspiranteComplementario::where('estado', 1)->count();
        $programasActivos = $this->programaRepository->countActivos();
        $tendenciaInscripciones = $this->aspiranteRepository->getTendenciaInscripciones(6);
        $distribucionProgramas = $this->aspiranteRepository->getDistribucionPorProgramas();

        $programasDemanda = $this->programaRepository->getProgramasConMayorDemanda(10)
            ->map(function($programa) {
                return [
                    'programa' => $programa->nombre,
                    'total_aspirantes' => $programa->total_aspirantes,
                    'aceptados' => $programa->aceptados,
                    'pendientes' => $programa->pendientes,
                    'tasa_aceptacion' => $programa->tasa_aceptacion
                ];
            });

        return [
            'total_aspirantes' => $totalAspirantes,
            'aspirantes_aceptados' => $aspirantesAceptados,
            'aspirantes_pendientes' => $aspirantesPendientes,
            'programas_activos' => $programasActivos,
            'tendencia_inscripciones' => $tendenciaInscripciones,
            'distribucion_programas' => $distribucionProgramas,
            'programas_demanda' => $programasDemanda
        ];
    }

    /**
     * Obtener estadísticas filtradas por criterios específicos
     */
    public function obtenerEstadisticasFiltradas(array $filtros): array
    {
        $query = AspiranteComplementario::with(['persona', 'complementario']);

        if (isset($filtros['fecha_inicio']) && isset($filtros['fecha_fin'])) {
            $query->whereBetween('created_at', [$filtros['fecha_inicio'], $filtros['fecha_fin']]);
        }

        if (isset($filtros['departamento_id'])) {
            $query->whereHas('persona', function($q) use ($filtros) {
                $q->where('departamento_id', $filtros['departamento_id']);
            });
        }

        if (isset($filtros['municipio_id'])) {
            $query->whereHas('persona', function($q) use ($filtros) {
                $q->where('municipio_id', $filtros['municipio_id']);
            });
        }

        if (isset($filtros['programa_id'])) {
            $query->where('complementario_id', $filtros['programa_id']);
        }

        return [
            'total_filtrado' => $query->count(),
            'aceptados_filtrado' => (clone $query)->where('estado', 3)->count(),
            'pendientes_filtrado' => (clone $query)->where('estado', 1)->count(),
            'datos' => $query->get()
        ];
    }

    /**
     * Generar reporte de tendencias mensuales
     */
    public function generarReporteTendencias(int $meses = 12)
    {
        return AspiranteComplementario::selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as total_inscripciones,
                SUM(CASE WHEN estado = 3 THEN 1 ELSE 0 END) as aceptados,
                SUM(CASE WHEN estado = 1 THEN 1 ELSE 0 END) as pendientes
            ')
            ->where('created_at', '>=', now()->subMonths($meses))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    /**
     * Obtener estadísticas por género
     */
    public function obtenerEstadisticasPorGenero()
    {
        return $this->personaRepository->getEstadisticasPorGenero();
    }

    /**
     * Obtener estadísticas por rango de edad
     */
    public function obtenerEstadisticasPorEdad()
    {
        return $this->personaRepository->getEstadisticasPorEdad();
    }

    /**
     * Exportar programas con mayor demanda a Excel
     */
    public function exportarProgramasDemandaExcel(): StreamedResponse
    {
        try {
            $estadisticas = $this->obtenerEstadisticasReales();
            $programasDemanda = $estadisticas['programas_demanda'];

            // Crear hoja de cálculo
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar título
            $sheet->setCellValue('A1', 'PROGRAMAS CON MAYOR DEMANDA');
            $sheet->mergeCells('A1:E1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '007BFF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Fecha de generación
            $sheet->setCellValue('A2', 'Fecha de generación: ' . now()->format('d/m/Y H:i:s'));
            $sheet->mergeCells('A2:E2');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['size' => 10, 'italic' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Encabezados de columnas
            $encabezados = [
                'A4' => 'Nombre del Programa',
                'B4' => 'Total Aspirantes',
                'C4' => 'Aceptados',
                'D4' => 'Pendientes',
                'E4' => 'Tasa de Aceptación (%)',
            ];

            foreach ($encabezados as $celda => $titulo) {
                $sheet->setCellValue($celda, $titulo);
            }

            // Estilo para encabezados
            $sheet->getStyle('A4:E4')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '6C757D'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Llenar datos
            $fila = 5;
            foreach ($programasDemanda as $programa) {
                $sheet->setCellValue('A' . $fila, $programa['programa']);
                $sheet->setCellValue('B' . $fila, $programa['total_aspirantes']);
                $sheet->setCellValue('C' . $fila, $programa['aceptados']);
                $sheet->setCellValue('D' . $fila, $programa['pendientes']);
                $sheet->setCellValue('E' . $fila, $programa['tasa_aceptacion']);

                // Alinear números a la derecha
                $sheet->getStyle('B' . $fila . ':E' . $fila)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);

                $fila++;
            }

            // Auto-ajustar anchos de columnas
            foreach (range('A', 'E') as $columna) {
                $sheet->getColumnDimension($columna)->setAutoSize(true);
            }

            // Agregar bordes a los datos
            $ultimaFila = $fila - 1;
            $sheet->getStyle('A4:E' . $ultimaFila)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
            ]);

            // Crear nombre del archivo
            $fileName = 'programas_mayor_demanda_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

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

            Log::info('Archivo Excel de programas con mayor demanda generado', [
                'archivo' => $fileName,
                'registros' => count($programasDemanda),
                'user_id' => auth()->id(),
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::error('Error exportando programas con mayor demanda a Excel', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'exception' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
