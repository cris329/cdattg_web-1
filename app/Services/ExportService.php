<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ExportService
{
    /**
     * Exporta datos a Excel
     *
     * @param Collection $datos
     * @param array $columnas
     * @param string $titulo
     * @return string Path del archivo
     */
    public function exportarExcel(Collection $datos, array $columnas, string $titulo = 'Reporte'): string
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Determinar columna inicial para el título (si la primera columna es ID, empezar en B)
            $primerCampo = $columnas[0]['field'] ?? null;
            $inicioTituloCol = (is_string($primerCampo) && strtolower($primerCampo) === 'id') ? 'B' : 'A';

            // Título
            $sheet->setCellValue($inicioTituloCol . '1', strtoupper($titulo));
            $sheet->mergeCells($inicioTituloCol . '1:' . chr(64 + count($columnas)) . '1');
            $sheet->getStyle($inicioTituloCol . '1')->getFont()->setBold(true)->setSize(14);

            // Fecha de generación
            $sheet->setCellValue($inicioTituloCol . '2', 'Fecha de generación: ' . now()->format('d/m/Y H:i:s'));

            // Encabezados de columnas
            $headerRow = 4;
            $col = 'A';
            foreach ($columnas as $columna) {
                $sheet->setCellValue($col . $headerRow, $columna['label']);
                $col++;
            }

            // Estilos de encabezado tipo tabla
            $lastColumnLetter = chr(64 + count($columnas));
            $headerRange = 'A' . $headerRow . ':' . $lastColumnLetter . $headerRow;

            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension($headerRow)->setRowHeight(20);

            // Datos
            $dataStartRow = 5;
            $row = $dataStartRow;
            foreach ($datos as $dato) {
                $col = 'A';
                foreach ($columnas as $columna) {
                    $valor = is_array($dato) ? ($dato[$columna['field']] ?? '') : ($dato->{$columna['field']} ?? '');
                    $sheet->setCellValue($col . $row, $valor);
                    $col++;
                }
                $row++;
            }

            $lastDataRow = $row - 1;

            // Aplicar estilo de filas alternas para simular tabla con bandas
            if ($lastDataRow >= $dataStartRow) {
                for ($currentRow = $dataStartRow; $currentRow <= $lastDataRow; $currentRow++) {
                    if ($currentRow % 2 === 0) {
                        $range = 'A' . $currentRow . ':' . $lastColumnLetter . $currentRow;
                        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID);
                        $sheet->getStyle($range)->getFill()->getStartColor()->setRGB('E9EDF5');
                    }
                }
            }

            // Agregar autofiltro a la fila de encabezados para comportamiento tipo tabla
            $sheet->setAutoFilter('A' . $headerRow . ':' . $lastColumnLetter . $headerRow);

            // Auto-ajustar anchos
            foreach (range('A', $lastColumnLetter) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $filename = 'exports/' . $titulo . '_' . time() . '.xlsx';
            $path = storage_path('app/public/' . $filename);

            // Crear directorio si no existe
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($path);

            Log::info('Archivo Excel generado', [
                'archivo' => $filename,
                'registros' => $datos->count(),
            ]);

            return $filename;
        } catch (\Exception $e) {
            Log::error('Error generando Excel', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Exporta datos a CSV
     *
     * @param Collection $datos
     * @param array $columnas
     * @param string $titulo
     * @return string Path del archivo
     */
    public function exportarCSV(Collection $datos, array $columnas, string $titulo = 'Reporte'): string
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Encabezados
            $col = 'A';
            foreach ($columnas as $columna) {
                $sheet->setCellValue($col . '1', $columna['label']);
                $col++;
            }

            // Datos
            $row = 2;
            foreach ($datos as $dato) {
                $col = 'A';
                foreach ($columnas as $columna) {
                    $valor = is_array($dato) ? ($dato[$columna['field']] ?? '') : ($dato->{$columna['field']} ?? '');
                    $sheet->setCellValue($col . $row, $valor);
                    $col++;
                }
                $row++;
            }

            $filename = 'exports/' . $titulo . '_' . time() . '.csv';
            $path = storage_path('app/public/' . $filename);

            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $writer = new Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->save($path);

            return $filename;
        } catch (\Exception $e) {
            Log::error('Error generando CSV', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Exporta datos a JSON
     *
     * @param Collection $datos
     * @param string $titulo
     * @return string Path del archivo
     */
    public function exportarJSON(Collection $datos, string $titulo = 'Reporte'): string
    {
        try {
            $filename = 'exports/' . $titulo . '_' . time() . '.json';
            $path = storage_path('app/public/' . $filename);

            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            file_put_contents($path, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return $filename;
        } catch (\Exception $e) {
            Log::error('Error generando JSON', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

