<?php

declare(strict_types=1);

namespace App\Http\Requests\Complementarios;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAspiranteRequest extends FormRequest
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
        $programaId = $this->route('programa') ?? $this->route('complementarioId');
        
        $rules = [
            'numero_documento' => [
                'required',
                'string',
                'max:191',
                // No validar existencia aquí, dejar que el servicio lo maneje para retornar mensaje personalizado
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];

        // Solo agregar validación de inscripción duplicada si hay un programa en la ruta
        if ($programaId !== null) {
            $rules['numero_documento'][] = function ($value, $fail) use ($programaId) {
                $persona = \App\Models\Persona::where('numero_documento', $value)->first();
                if (!$persona) {
                    // No validar existencia aquí, dejar que el servicio lo maneje
                    return;
                }

                $existeInscripcion = \App\Models\Complementarios\AspiranteComplementario::where('persona_id', $persona->id)
                    ->where('complementario_id', $programaId)
                    ->exists();

                if ($existeInscripcion) {
                    $fail('La persona con este número de documento ya se encuentra inscrita en este programa complementario.');
                }
            };
        }
        
        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.string' => 'El número de documento debe ser una cadena de texto.',
            'numero_documento.max' => 'El número de documento no puede exceder los 191 caracteres.',
            'numero_documento.exists' => 'No se encontró ninguna persona registrada con este número de documento.',
            'observaciones.string' => 'Las observaciones deben ser una cadena de texto.',
            'observaciones.max' => 'Las observaciones no pueden exceder los 500 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('numero_documento')) {
            $this->merge([
                'numero_documento' => trim($this->numero_documento),
            ]);
        }
    }
}

