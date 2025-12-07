<?php

declare(strict_types=1);

namespace App\Http\Requests\Complementarios;

use Illuminate\Foundation\Http\FormRequest;

class RechazarAspiranteRequest extends FormRequest
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
     * Este Form Request valida los datos para rechazar un aspirante.
     * Implementa el caso de uso RF-ASP-004: Rechazar Aspirante.
     * Nota: Actualmente el rechazo no requiere datos del request,
     * pero este Form Request está preparado para futuras mejoras
     * como agregar motivo de rechazo o comentarios.
     */
    public function rules(): array
    {
        return [
            'motivo_rechazo' => [
                'nullable',
                'string',
                'max:500',
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'motivo_rechazo.max' => 'El motivo de rechazo no puede exceder los 500 caracteres.',
            'observaciones.max' => 'Las observaciones no pueden exceder los 500 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('motivo_rechazo') && $this->motivo_rechazo !== null) {
            $this->merge(['motivo_rechazo' => trim($this->motivo_rechazo)]);
        }
        if ($this->has('observaciones') && $this->observaciones !== null) {
            $this->merge(['observaciones' => trim($this->observaciones)]);
        }
    }
}

