<?php

namespace App\Http\Requests\Complementarios;

use Illuminate\Foundation\Http\FormRequest;

class AspiranteRequest extends FormRequest
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
            'numero_documento' => 'required|string|max:191',
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.exists' => 'No se encontró ninguna persona registrada con este número de documento.',
            'observaciones.max' => 'Las observaciones no pueden exceder los 500 caracteres.',
        ];
    }
}
