<?php

namespace App\Http\Requests\Complementarios;

use App\Configuration\UploadLimits;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class CatalogoComplementarioImportRequest extends FormRequest
{
    private const MAX_FILE_SIZE_KB = UploadLimits::IMPORT_FILE_SIZE_KB;
    private const MAX_CONTENT_LENGTH_BYTES = UploadLimits::IMPORT_CONTENT_LENGTH_BYTES;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'archivo_catalogo' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:' . self::MAX_FILE_SIZE_KB,
                function (string $attribute, $value, $fail): void {
                    /** @var \Illuminate\Http\UploadedFile|null $uploadedFile */
                    $uploadedFile = $value instanceof \Illuminate\Http\UploadedFile
                        ? $value
                        : $this->file($attribute);

                    $this->validateFileIntegrity($uploadedFile, $fail);
                },
            ],
        ];
    }

    public function messages(): array
    {
        $maxSizeMb = UploadLimits::IMPORT_FILE_SIZE_MB;

        return [
            'archivo_catalogo.required' => 'Debes seleccionar el archivo del catálogo.',
            'archivo_catalogo.file' => 'El archivo proporcionado no es válido.',
            'archivo_catalogo.mimes' => 'El archivo debe ser de tipo Excel (.xlsx o .xls).',
            'archivo_catalogo.max' => "El archivo no debe superar los {$maxSizeMb}MB.",
        ];
    }

    private function validateFileIntegrity(?\Illuminate\Http\UploadedFile $file, callable $fail): void
    {
        if ($file === null || !$file->isValid()) {
            $fail('El archivo no es válido o está corrupto.');
            return;
        }

        $realSize = $file->getSize();

        if (!UploadLimits::isWithinLimit($realSize, UploadLimits::IMPORT_FILE_SIZE_BYTES)) {
            $sizeMb = round($realSize / 1024 / 1024, 2);
            $maxMb = UploadLimits::IMPORT_FILE_SIZE_MB;
            $fail("El tamaño real del archivo ({$sizeMb}MB) excede el límite permitido de {$maxMb}MB.");
            return;
        }

        if ($realSize === 0) {
            $fail('El archivo está vacío.');
            return;
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        if (!UploadLimits::isValidExcelMimeType($extension, $mimeType)) {
            $fail("El tipo MIME del archivo ({$mimeType}) no coincide con la extensión ({$extension}).");
        }
    }

    protected function prepareForValidation(): void
    {
        $this->assertSafeContentLength($this->header('Content-Length'));
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Error de validación en el archivo de catálogo.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    private function assertSafeContentLength(?string $contentLengthHeader): void
    {
        if ($contentLengthHeader === null) {
            return;
        }

        $contentLength = filter_var(
            $contentLengthHeader,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0]]
        );

        if ($contentLength === false) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'El encabezado Content-Length es inválido.',
                    'errors' => [
                        'content_length' => ['El valor recibido debe ser un entero positivo.'],
                    ],
                ], Response::HTTP_BAD_REQUEST)
            );
        }

        if ($contentLength > self::MAX_CONTENT_LENGTH_BYTES) {
            $maxSize = UploadLimits::formatBytes(self::MAX_CONTENT_LENGTH_BYTES);

            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => "El tamaño de la petición excede el límite permitido de {$maxSize}.",
                    'max_size_bytes' => self::MAX_CONTENT_LENGTH_BYTES,
                    'request_size_bytes' => $contentLength,
                ], Response::HTTP_REQUEST_ENTITY_TOO_LARGE)
            );
        }
    }
}


