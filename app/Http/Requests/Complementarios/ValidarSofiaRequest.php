<?php

declare(strict_types=1);

namespace App\Http\Requests\Complementarios;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidarSofiaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {

        return [
            'complementario_id' => [
                'sometimes',
                'integer',
                Rule::exists('complementarios_ofertados', 'id'),
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $complementarioId = $this->route('complementarioId') ?? $this->route('programa');

            if ($complementarioId === null) {
                $validator->errors()->add('complementario_id', 'ID del programa no proporcionado.');
                return;
            }

            // Validar que exista el programa
            $programa = \App\Models\Complementarios\ComplementarioOfertado::find($complementarioId);
            if (!$programa) {
                $validator->errors()->add('complementario_id', 'Programa no encontrado.');
                return;
            }

            // Contar aspirantes que necesitan validación
            $aspirantesCount = \App\Models\Complementarios\AspiranteComplementario::with('persona')
                ->where('complementario_id', $complementarioId)
                ->whereHas('persona', function ($query) {
                    $query->whereIn('estado_sofia', [277, 279]); // NO REGISTRADO (277) o REQUIERE CAMBIO (279)
                })
                ->count();

            if ($aspirantesCount === 0) {
                $validator->errors()->add('complementario_id', 'No hay aspirantes que necesiten validación en este programa.');
                return;
            }

            // Verificar si ya hay una validación en progreso
            $existingProgress = \App\Models\Complementarios\SofiaValidationProgress::where('complementario_id', $complementarioId)
                ->whereIn('status', [284, 285]) // PENDING (284) o PROCESSING (285)
                ->first();

            if ($existingProgress) {
                $validator->errors()->add('complementario_id', 'Ya hay una validación en progreso para este programa. Espere a que termine.');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'complementario_id.exists' => 'El programa especificado no existe.',
            'complementario_id.integer' => 'El ID del programa debe ser un número entero.',
        ];
    }
}
