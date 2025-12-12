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
    private const COLOR_NEGRO_RGB = '000000';

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
            $pdf = $this->crearFpdi();

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
        $this->establecerEncabezados($sheet);

        // Llenar datos
        $this->llenarDatos($sheet, $aspirantes);

        // Configurar alturas de filas
        $sheet->getRowDimension(1)->setRowHeight(15);
        $sheet->getRowDimension(2)->setRowHeight(45);

        $calibriStyle = [
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
            ],
        ];
        $sheet->getStyle('A1:G' . ($sheet->getHighestRow()))->applyFromArray($calibriStyle);

        // Aplicar tamaño de letra 8 y ajuste de texto para filas 2 en adelante
        $dataStyle = [
            'font' => [
                'size' => 8,
            ],
            'alignment' => [
                'wrapText' => true,
            ],
        ];
        $sheet->getStyle('A2:G' . ($sheet->getHighestRow()))->applyFromArray($dataStyle);

        // Configurar anchos de columna específicos
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(10);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(40);
        $sheet->getColumnDimension('G')->setWidth(10);

        return $spreadsheet;
    }

    /**
     * Establecer encabezados de la hoja
     */
    private function establecerEncabezados($sheet): void
    {
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
    }

    /**
     * Llenar datos en la hoja
     */
    private function llenarDatos($sheet, $aspirantes): void
    {
        $row = 3;
        foreach ($aspirantes as $aspirante) {
            $tipoDocumento = $aspirante->persona->tipoDocumento ? $aspirante->persona->tipoDocumento->parametro->name : 'N/A';
            $numeroDocumento = $aspirante->persona->numero_documento;
            
            // Obtener caracterización
            $caracterizacion = $aspirante->persona->caracterizacion ?
                $aspirante->persona->caracterizacion->name : 'Sin caracterización';

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
     * Crear instancia de Fpdi
     * Método protegido para facilitar el testing
     */
    protected function crearFpdi(): Fpdi
    {
        return new Fpdi();
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
