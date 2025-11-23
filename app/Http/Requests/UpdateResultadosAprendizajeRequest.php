<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResultadosAprendizajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('resultados_aprendizajes', 'codigo')->ignore($this->route('resultado_aprendizaje'))
            ],
            'nombre' => 'required|string|max:500',
            'competencia_id' => 'nullable|exists:competencias,id',
            'status' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.string' => 'El código debe ser una cadena de texto.',
            'codigo.max' => 'El código no puede tener más de 50 caracteres.',
            'codigo.unique' => 'Este código ya está registrado en el sistema. Por favor use uno diferente.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede tener más de 500 caracteres.',
            'competencia_id.exists' => 'La competencia seleccionada no existe en el sistema.',
            'status.boolean' => 'El estado debe ser verdadero o falso.',
        ];
    }

    public function attributes(): array
    {
        return [
            'codigo' => 'código',
            'nombre' => 'nombre',
            'duracion' => 'duración',
            'competencia_id' => 'competencia',
            'status' => 'estado',
        ];
    }
}
