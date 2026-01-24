<?php

namespace App\Services\Complementarios;

use App\Models\Complementarios\ComplementarioCatalogo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CatalogoComplementarioImportService
{
    private const NIVEL_CURSO_ESPECIAL = 'CURSO ESPECIAL';

    public function importarCatalogo(UploadedFile $file): int
    {
        $rutaArchivo = $this->almacenarArchivoTemporal($file);

        try {
            return $this->procesarArchivo($rutaArchivo);
        } finally {
            Storage::disk('local')->delete($rutaArchivo);
        }
    }

    private function almacenarArchivoTemporal(UploadedFile $file): string
    {
        $timestamp = now()->format('Ymd_His');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'xlsx');
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $safeBaseName = $baseName !== ''
            ? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $baseName)
            : 'catalogo';

        $fileName = "{$timestamp}_catalogo_{$safeBaseName}.{$extension}";

        return $file->storeAs('catalogo_complementarios', $fileName, 'local');
    }

    private function procesarArchivo(string $rutaRelativa): int
    {
        $rutaAbsoluta = Storage::disk('local')->path($rutaRelativa);

        if (!file_exists($rutaAbsoluta)) {
            throw new \RuntimeException("No se encontró el archivo de catálogo en {$rutaAbsoluta}.");
        }

        $reader = IOFactory::createReaderForFile($rutaAbsoluta);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($rutaAbsoluta);
        $hoja = $spreadsheet->getActiveSheet();
        $rows = $hoja->toArray(null, true, true, true);

        unset($hoja);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        if (count($rows) <= 1) {
            return 0;
        }

        $encabezados = array_shift($rows);

        $mapaColumnas = $this->construirMapaColumnas($encabezados);

        $actualizados = 0;

        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                $datos = $this->mapearFila($row, $mapaColumnas);

                if ($datos === null) {
                    continue;
                }

                $actualizados += $this->upsertPrograma($datos);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Error importando catálogo de complementarios', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            throw $e;
        }

        return $actualizados;
    }

    /**
     * @param array<string, mixed> $encabezados
     * @return array<string, string>
     */
    private function construirMapaColumnas(array $encabezados): array
    {
        $mapa = [];

        foreach ($encabezados as $columna => $titulo) {
            if (!is_string($titulo)) {
                continue;
            }

            $tituloNormalizado = mb_strtoupper(trim($titulo));

            $mapa[$tituloNormalizado] = $columna;
        }

        return $mapa;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, string> $mapaColumnas
     * @return array<string, mixed>|null
     */
    private function mapearFila(array $row, array $mapaColumnas): ?array
    {
        $nivel = $this->obtenerValor($row, $mapaColumnas, 'NIVEL DE FORMACION');

        if ($nivel === null || mb_strtoupper(trim((string) $nivel)) !== self::NIVEL_CURSO_ESPECIAL) {
            return null;
        }

        $codigo = $this->obtenerValor($row, $mapaColumnas, 'PRF_CODIGO');
        $version = $this->obtenerValor($row, $mapaColumnas, 'PRF_VERSION');
        $codVer = $this->obtenerValor($row, $mapaColumnas, 'COD_VER');
        $denominacion = $this->obtenerValor($row, $mapaColumnas, 'PRF_DENOMINACION');
        $duracion = $this->obtenerValor($row, $mapaColumnas, 'PRF_DURACION_MAXIMA');

        if ($codigo === null || $denominacion === null || $duracion === null) {
            return null;
        }

        $requisitos = $this->obtenerValor($row, $mapaColumnas, 'PRF_DESCRIPCION_REQUISITO');

        return [
            'prf_codigo' => (string) $codigo,
            'version' => $this->toInt($version, 1),
            'cod_ver' => $codVer !== null ? (string) $codVer : null,
            'denominacion' => (string) $denominacion,
            'nivel_formacion' => (string) $nivel,
            'duracion_horas' => $this->toInt($duracion, 0),
            'requisitos_ingreso' => $this->limpiarTexto($requisitos),
            'linea_tecnologica' => $this->toNullableString(
                $this->obtenerValor($row, $mapaColumnas, 'LINEA TECNOLÓGICA')
                    ?? $this->obtenerValor($row, $mapaColumnas, 'LINEA TECNOLÓGICA ')
                    ?? $this->obtenerValor($row, $mapaColumnas, 'LINEA TECNOLÓGICA')
            ),
            'red_tecnologica' => $this->toNullableString($this->obtenerValor($row, $mapaColumnas, 'RED TECNOLÓGICA')),
            'red_conocimiento' => $this->toNullableString($this->obtenerValor($row, $mapaColumnas, 'RED DE CONOCIMIENTO')),
            'modalidad_id' => $this->convertirModalidadAId($this->obtenerValor($row, $mapaColumnas, 'MODALIDAD')),
            'apuesta_prioritaria' => $this->toNullableString($this->obtenerValor($row, $mapaColumnas, 'APUESTAS PRIORITARIAS')),
            'tipo_permiso' => $this->toNullableString($this->obtenerValor($row, $mapaColumnas, 'TIPO PERMISO')),
            'multiple_inscripcion' => $this->toBoolSiNo(
                $this->obtenerValor($row, $mapaColumnas, 'MULTIPLE INSCRIPCION')
            ),
            'alamedida' => $this->toBoolSiNo(
                $this->obtenerValor($row, $mapaColumnas, 'PRF_ALAMEDIDA')
            ),
            'fic' => $this->toBoolSiNo(
                $this->obtenerValor($row, $mapaColumnas, 'FIC')
            ),
            'creditos' => $this->toInt(
                $this->obtenerValor($row, $mapaColumnas, 'PRF_CREDITOS'),
                0
            ),
            'indice' => $this->toNullableString($this->obtenerValor($row, $mapaColumnas, 'INDICE')),
            'ocupacion' => $this->toNullableString($this->obtenerValor($row, $mapaColumnas, 'OCUPACIÓN')),
        ];
    }

    private function toBoolSiNo(mixed $valor): bool
    {
        if ($valor === null) {
            return false;
        }

        $normalizado = mb_strtoupper(trim((string) $valor));

        return $normalizado === 'SI';
    }

    private function toInt(mixed $valor, int $porDefecto = 0): int
    {
        if ($valor === null || $valor === '') {
            return $porDefecto;
        }

        if (is_int($valor)) {
            return $valor;
        }

        if (is_float($valor)) {
            return (int) round($valor);
        }

        if (!is_numeric($valor)) {
            return $porDefecto;
        }

        return (int) $valor;
    }

    private function limpiarTexto(mixed $valor): ?string
    {
        $texto = $this->toNullableString($valor);

        if ($texto === null) {
            return null;
        }

        $texto = str_replace(["\r\n", "\r"], "\n", $texto);
        $texto = str_replace(["_x000D_", "\t"], ' ', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto) ?? $texto;

        return trim($texto) !== '' ? trim($texto) : null;
    }

    private function toNullableString(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto !== '' ? $texto : null;
    }

    private function obtenerValor(array $row, array $mapaColumnas, string $titulo): mixed
    {
        $clave = mb_strtoupper(trim($titulo));

        if (!array_key_exists($clave, $mapaColumnas)) {
            return null;
        }

        $columna = $mapaColumnas[$clave];

        return $row[$columna] ?? null;
    }

    /**
     * @param array<string, mixed> $datos
     */
    private function upsertPrograma(array $datos): int
    {
        $codigo = $datos['prf_codigo'];
        $nuevaVersion = (int) $datos['version'];

        /** @var ComplementarioCatalogo|null $existente */
        $existente = ComplementarioCatalogo::query()
            ->where('prf_codigo', $codigo)
            ->first();

        if ($existente === null) {
            ComplementarioCatalogo::query()->create($datos);

            return 1;
        }

        if ((int) $existente->version >= $nuevaVersion) {
            return 0;
        }

        $existente->fill($datos);
        $existente->save();

        return 1;
    }

    /**
     * Convierte el string de modalidad a modalidad_id (ParametroTema)
     * 
     * @param mixed $modalidadString
     * @return int|null
     */
    private function convertirModalidadAId(mixed $modalidadString): ?int
    {
        if ($modalidadString === null || $modalidadString === '') {
            return null;
        }

        $modalidadNormalizada = mb_strtoupper(trim((string) $modalidadString));
        
        // Mapeo de valores de modalidad string a parametro_id
        $mapeoModalidad = [
            'PRESENCIAL' => 18,
            'VIRTUAL' => 19,
            'MIXTA' => 20,
        ];

        $parametroId = $mapeoModalidad[$modalidadNormalizada] ?? null;
        
        if (!$parametroId) {
            return null;
        }

        // Buscar el ParametroTema correspondiente (Tema ID 5 = MODALIDADES DE FORMACION)
        $parametroTema = DB::table('parametros_temas')
            ->where('tema_id', 5)
            ->where('parametro_id', $parametroId)
            ->first();

        return $parametroTema?->id;
    }
}


