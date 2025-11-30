<?php

namespace App\Services\Complementarios;

use App\Models\Persona;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class AspiranteDocumentoService
{
    /**
     * Construir patrón de búsqueda para documento
     */
    public function construirPatronBusqueda(Persona $persona): string
    {
        $tipoDocumento = $persona->tipoDocumento ? str_replace(
            ' ',
            '_',
            $persona->tipoDocumento->name
        ) : 'DOC';

        return "{$tipoDocumento}_{$persona->numero_documento}_" .
            str_replace(' ', '_', $persona->primer_nombre) . "_" .
            str_replace(' ', '_', $persona->primer_apellido) . "_";
    }

    /**
     * Buscar documento en Google Drive
     */
    public function buscarDocumentoEnGoogleDrive(array $files, string $patron): bool
    {
        // Crear variantes del patrón para manejar diferentes formatos
        $patrones = [$patron];

        // Si el patrón tiene guiones bajos, crear versión con espacios
        if (strpos($patron, '_') !== false) {
            $patronConEspacios = str_replace('_', ' ', $patron);
            $patrones[] = $patronConEspacios;
        }

        // Si el patrón tiene espacios, crear versión con guiones bajos
        if (strpos($patron, ' ') !== false) {
            $patronConGuiones = str_replace(' ', '_', $patron);
            $patrones[] = $patronConGuiones;
        }

        // Crear patrón alternativo sin nombres (solo tipo_documento + numero_documento)
        // Esto para manejar archivos subidos desde procesar-documentos
        $patronSinNombres = $this->crearPatronSinNombres($patron);
        if ($patronSinNombres) {
            $patrones[] = $patronSinNombres;

            // También crear versión con espacios
            $patronSinNombresConEspacios = str_replace('_', ' ', $patronSinNombres);
            $patrones[] = $patronSinNombresConEspacios;
        }

        foreach ($files as $file) {
            $fileName = basename($file);

            // Buscar archivos que contengan cualquiera de los patrones
            foreach ($patrones as $patronActual) {
                if (strpos($fileName, $patronActual) !== false) {
                    try {
                        if (Storage::disk('google')->exists($file)) {
                            Log::info("Documento encontrado en Google Drive", [
                                'archivo' => $fileName,
                                'patron_usado' => $patronActual,
                                'patron_original' => $patron
                            ]);
                            return true;
                        }
                    } catch (\Exception $e) {
                        Log::warning("Error verificando existencia de archivo: {$fileName}", [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }

        Log::warning("Documento no encontrado en Google Drive", [
            'patron' => $patron,
            'patrones_buscados' => $patrones,
            'total_archivos' => count($files)
        ]);
        return false;
    }

    /**
     * Crear patrón sin nombres para buscar archivos subidos desde procesar-documentos
     * Extrae solo tipo_documento + numero_documento del patrón completo
     */
    private function crearPatronSinNombres(string $patron): ?string
    {
        // El patrón completo es: tipo_documento_numero_documento_primer_nombre_primer_apellido_
        // Queremos extraer: tipo_documento_numero_documento_

        // Dividir por guiones bajos
        $partes = explode('_', $patron);

        // Necesitamos al menos tipo_documento, numero_documento y nombres
        if (count($partes) < 4) {
            return null;
        }

        // El tipo de documento puede tener múltiples partes (ej: CÉDULA_DE_CIUDADANÍA)
        // Buscamos el número de documento (debe ser numérico)
        $numeroDocumentoIndex = null;
        for ($i = 0; $i < count($partes); $i++) {
            if (is_numeric($partes[$i])) {
                $numeroDocumentoIndex = $i;
                break;
            }
        }

        if ($numeroDocumentoIndex === null) {
            return null;
        }

        // Reconstruir desde el inicio hasta el número de documento
        $patronSinNombres = '';
        for ($i = 0; $i <= $numeroDocumentoIndex; $i++) {
            $patronSinNombres .= $partes[$i] . '_';
        }

        Log::info("Patrón sin nombres creado", [
            'patron_original' => $patron,
            'patron_sin_nombres' => $patronSinNombres
        ]);

        return $patronSinNombres;
    }

    /**
     * Obtener archivos de Google Drive
     */
    public function getGoogleDriveFiles(): array
    {
        try {
            $files = Storage::disk('google')->files('documentos_aspirantes');
            Log::info("Total de archivos en Google Drive: " . count($files));
            return $files;
        } catch (\Exception $e) {
            Log::error("Error al listar archivos en Google Drive: " . $e->getMessage());
            throw new \RuntimeException('Error al acceder a Google Drive: ' . $e->getMessage());
        }
    }

    /**
     * Encontrar archivo en Google Drive
     */
    public function encontrarArchivoEnGoogleDrive(string $patron): ?string
    {
        $files = Storage::disk('google')->files('documentos_aspirantes');

        // Crear variantes del patrón para manejar diferentes formatos
        $patrones = [$patron];

        // Si el patrón tiene guiones bajos, crear versión con espacios
        if (strpos($patron, '_') !== false) {
            $patronConEspacios = str_replace('_', ' ', $patron);
            $patrones[] = $patronConEspacios;
        }

        // Si el patrón tiene espacios, crear versión con guiones bajos
        if (strpos($patron, ' ') !== false) {
            $patronConGuiones = str_replace(' ', '_', $patron);
            $patrones[] = $patronConGuiones;
        }

        // Crear patrón alternativo sin nombres (solo tipo_documento + numero_documento)
        // Esto para manejar archivos subidos desde procesar-documentos
        $patronSinNombres = $this->crearPatronSinNombres($patron);
        if ($patronSinNombres) {
            $patrones[] = $patronSinNombres;

            // También crear versión con espacios
            $patronSinNombresConEspacios = str_replace('_', ' ', $patronSinNombres);
            $patrones[] = $patronSinNombresConEspacios;
        }

        foreach ($files as $file) {
            $fileName = basename($file);

            // Buscar archivos que contengan cualquiera de los patrones
            foreach ($patrones as $patronActual) {
                if (strpos($fileName, $patronActual) !== false) {
                    Log::info("Archivo encontrado para descarga", [
                        'archivo' => $fileName,
                        'patron_usado' => $patronActual,
                        'patron_original' => $patron
                    ]);
                    return $file;
                }
            }
        }

        Log::warning("Archivo no encontrado para descarga", [
            'patron' => $patron,
            'patrones_buscados' => $patrones,
            'total_archivos' => count($files)
        ]);
        return null;
    }

    /**
     * Agregar páginas al PDF
     */
    public function agregarPaginasAPDF(Fpdi $pdf, string $tempFilePath): void
    {
        $pageCount = $pdf->setSourceFile($tempFilePath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
        }
    }

    /**
     * Limpiar archivos temporales
     */
    public function limpiarArchivosTemporales(array $archivosTemporales): void
    {
        foreach ($archivosTemporales as $tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * Crear directorio temporal
     */
    public function createTempDirectory(): string
    {
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        return $tempDir;
    }
}
