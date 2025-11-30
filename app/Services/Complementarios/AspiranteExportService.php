<?php

namespace App\Services\Complementarios;

use App\Exceptions\AspirantesSinDocumentosException;
use App\Exceptions\DescargaDocumentosException;
use App\Exceptions\ProgramaNoEncontradoException;
use App\Models\Complementarios\ComplementarioOfertado;
use App\Repositories\Complementarios\AspiranteComplementarioRepository;
use App\Repositories\Complementarios\ComplementarioOfertadoRepository;
use App\Services\Complementarios\AspiranteComplementarioService;
use App\Services\Complementarios\AspiranteDocumentoService;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use setasign\Fpdi\Fpdi;

class AspiranteExportService
{
    public function __construct(
        private readonly AspiranteComplementarioRepository $aspiranteRepository,
        private readonly ComplementarioOfertadoRepository $programaRepository,
        private readonly AspiranteComplementarioService $aspiranteComplementarioService,
        private readonly AspiranteDocumentoService $documentoService
    ) {}

    /**
     * Exportar aspirantes a Excel
     */
    public function exportarAspirantesExcel(int $complementarioId): StreamedResponse
    {
        try {
            // Verificar que el programa existe
            $programa = $this->programaRepository->findWithRelations($complementarioId);
            if (!$programa) {
                throw new ProgramaNoEncontradoException('Programa no encontrado');
            }

            // Obtener aspirantes para exportación
            $aspirantes = $this->aspiranteRepository->findForExport($complementarioId);

            // Crear hoja de cálculo
            $spreadsheet = $this->crearHojaCalculo($aspirantes);

            // Crear nombre del archivo
            $fileName = 'aspirantes_' . str_replace(' ', '_', $programa->nombre) . '_' .
                now()->format('Y-m-d_H-i-s') . '.xlsx';

            // Crear respuesta de descarga
            return $this->crearRespuestaDescarga($spreadsheet, $fileName);

        } catch (\Exception $e) {
            Log::error('Error exportando aspirantes a Excel: ' . $e->getMessage(), [
                'complementario_id' => $complementarioId,
                'user_id' => auth()->id(),
                'exception' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Descargar cédulas de aspirantes en PDF
     */
    public function descargarCedulas(int $complementarioId)
    {
        try {
            // Verificar que el programa existe
            $programa = $this->programaRepository->findWithRelations($complementarioId);
            if (!$programa) {
                throw new ProgramaNoEncontradoException('Programa no encontrado');
            }

            // Obtener aspirantes con documentos
            $aspirantes = $this->aspiranteRepository->findByProgramaConDocumentos($complementarioId);

            if ($aspirantes->isEmpty()) {
                throw new AspirantesSinDocumentosException('No hay aspirantes con documentos de identidad para descargar.');
            }

            // Crear directorio temporal y PDF
            $tempDir = $this->documentoService->createTempDirectory();
            $pdf = new Fpdi();

            // Procesar documentos
            $resultados = $this->aspiranteComplementarioService->procesarDescargaDocumentos($aspirantes, $pdf, $tempDir);

            if ($resultados['archivos_agregados'] === 0) {
                $this->documentoService->limpiarArchivosTemporales($resultados['archivos_temporales']);
                throw new DescargaDocumentosException('No se pudieron descargar los documentos. Verifique que los archivos existan en Google Drive.');
            }

            // Generar archivo PDF final
            return $this->aspiranteComplementarioService->generarArchivoPDF(
                $programa,
                $pdf,
                $tempDir,
                $resultados['archivos_temporales']
            );

        } catch (\Exception $e) {
            Log::error('Error descargando cédulas: ' . $e->getMessage(), [
                'complementario_id' => $complementarioId,
                'user_id' => auth()->id(),
                'exception' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Crear hoja de cálculo con datos de aspirantes
     */
    private function crearHojaCalculo($aspirantes): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Establecer encabezados
        $this->establecerEncabezados($sheet);

        // Llenar datos
        $this->llenarDatos($sheet, $aspirantes);

        // Auto-ajustar columnas
        $this->autoAjustarColumnas($sheet);

        return $spreadsheet;
    }

    /**
     * Establecer encabezados de la hoja
     */
    private function establecerEncabezados($sheet): void
    {
        $sheet->setCellValue('A1', 'Tipo Documento');
        $sheet->setCellValue('B1', 'Número Documento');
        $sheet->setCellValue('C1', 'Caracterización');

        // Estilo para encabezados
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '007BFF'],
            ],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);
    }

    /**
     * Llenar datos en la hoja
     */
    private function llenarDatos($sheet, $aspirantes): void
    {
        $row = 2;
        foreach ($aspirantes as $aspirante) {
            $tipoDocumento = $aspirante->persona->tipoDocumento ? $aspirante->persona->tipoDocumento->name : 'N/A';
            $numeroDocumento = $aspirante->persona->numero_documento;
            $caracterizacion = $aspirante->persona->caracterizacion ?
                $aspirante->persona->caracterizacion->nombre : 'Sin caracterización';

            $sheet->setCellValue('A' . $row, $tipoDocumento);
            $sheet->setCellValue('B' . $row, $numeroDocumento);
            $sheet->setCellValue('C' . $row, $caracterizacion);

            $row++;
        }
    }

    /**
     * Auto-ajustar columnas
     */
    private function autoAjustarColumnas($sheet): void
    {
        foreach (range('A', 'C') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    /**
     * Crear respuesta de descarga
     */
    private function crearRespuestaDescarga(Spreadsheet $spreadsheet, string $fileName): StreamedResponse
    {
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
    }
}
