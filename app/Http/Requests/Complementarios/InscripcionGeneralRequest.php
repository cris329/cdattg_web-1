<?php

namespace App\Http\Requests\Complementarios;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class InscripcionGeneralRequest extends FormRequest
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
            'tipo_documento' => 'required|integer|exists:parametros,id',
            'numero_documento' => 'required|string|max:191|unique:personas,numero_documento',
            'primer_nombre' => 'required|string|max:191',
            'segundo_nombre' => 'nullable|string|max:191',
            'primer_apellido' => 'required|string|max:191',
            'segundo_apellido' => 'nullable|string|max:191',
            'fecha_nacimiento' => [
                'required',
                'date',
                // @phpstan-ignore-next-line Parameter required by Laravel validation closure signature
                function ($attribute, $value, $fail) {
                    $fechaNacimiento = Carbon::parse($value);
                    $edadMinima = Carbon::now()->subYears(14);

                    if ($fechaNacimiento->gt($edadMinima)) {
                        $fail('Debe tener al menos 14 años para registrarse.');
                    }
                },
            ],
            'genero' => 'required|integer|exists:parametros,id',
            'telefono' => 'nullable|string|max:191',
            'celular' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:personas,email',
            'pais_id' => 'required|integer|exists:pais,id',
            'departamento_id' => 'required|integer|exists:departamentos,id',
            'municipio_id' => 'required|integer|exists:municipios,id',
            'direccion' => 'nullable|string|max:191',
            'observaciones' => 'nullable|string',
            'parametro_id' => 'nullable|exists:parametros,id',
            'nivel_escolaridad_id' => 'nullable|exists:parametros,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'tipo_documento.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento.exists' => 'El tipo de documento seleccionado no es válido.',
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.unique' => 'Ya existe una persona registrada con este número de documento.',
            'primer_nombre.required' => 'El primer nombre es obligatorio.',
            'primer_apellido.required' => 'El primer apellido es obligatorio.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'genero.required' => 'El género es obligatorio.',
            'genero.exists' => 'El género seleccionado no es válido.',
            'celular.required' => 'El número de celular es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe tener un formato válido.',
            'email.unique' => 'Ya existe una persona registrada con este correo electrónico.',
            'pais_id.required' => 'El país es obligatorio.',
            'pais_id.exists' => 'El país seleccionado no es válido.',
            'departamento_id.required' => 'El departamento es obligatorio.',
            'departamento_id.exists' => 'El departamento seleccionado no es válido.',
            'municipio_id.required' => 'El municipio es obligatorio.',
            'municipio_id.exists' => 'El municipio seleccionado no es válido.',
        ];
    }
}
