<?php

declare(strict_types=1);

namespace App\Http\Requests\Complementarios;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class CreateAspiranteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     *
     * Este Form Request valida los datos para crear una nueva persona
     * y agregarla como aspirante a un programa complementario.
     * Implementa el caso de uso: Crear Nuevo Aspirante.
     */
    public function rules(): array
    {
        return [
            // Datos de identificación
            // Aceptar tanto 'tipo_documento' (del formulario) como 'tipo_documento_id' (para compatibilidad)
            'tipo_documento' => [
                'required_without:tipo_documento_id',
                'integer',
                Rule::exists('parametros', 'id'),
            ],
            'tipo_documento_id' => [
                'required_without:tipo_documento',
                'integer',
                Rule::exists('parametros', 'id'),
            ],
            'numero_documento' => [
                'required',
                'string',
                'max:191',
                Rule::unique('personas', 'numero_documento'),
            ],

            // Datos personales
            'primer_nombre' => [
                'required',
                'string',
                'max:191',
            ],
            'segundo_nombre' => [
                'nullable',
                'string',
                'max:191',
            ],
            'primer_apellido' => [
                'required',
                'string',
                'max:191',
            ],
            'segundo_apellido' => [
                'nullable',
                'string',
                'max:191',
            ],
            'fecha_nacimiento' => [
                'nullable',
                'date',
                'before:today',
            ],

            // Datos de género
            'genero_id' => [
                'nullable',
                'integer',
                Rule::exists('parametros', 'id'),
            ],

            // Datos de contacto
            'telefono' => [
                'nullable',
                'string',
                'max:191',
            ],
            'celular' => [
                'nullable',
                'string',
                'max:191',
            ],
            'email' => [
                'nullable',
                'email',
                'max:191',
                Rule::unique('personas', 'email'),
            ],

            // Datos de ubicación
            'pais_id' => [
                'nullable',
                'integer',
                Rule::exists('pais', 'id'),
            ],
            'departamento_id' => [
                'nullable',
                'integer',
                Rule::exists('departamentos', 'id'),
            ],
            'municipio_id' => [
                'nullable',
                'integer',
                Rule::exists('municipios', 'id'),
            ],
            'direccion' => [
                'nullable',
                'string',
                'max:191',
            ],

            // Caracterizaciones complementarias
            'caracterizaciones' => [
                'nullable',
                'array',
            ],
            'caracterizaciones.*' => [
                'integer',
                Rule::exists('parametros', 'id'),
            ],

            // Nivel de escolaridad
            'nivel_escolaridad_id' => [
                'nullable',
                'integer',
                Rule::exists('parametros_temas', 'id'),
            ],

            // Observaciones del aspirante
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
            // Tipo de documento
            'tipo_documento.required_without' => 'El tipo de documento es obligatorio.',
            'tipo_documento.exists' => 'El tipo de documento seleccionado no es válido.',
            'tipo_documento_id.required_without' => 'El tipo de documento es obligatorio.',
            'tipo_documento_id.exists' => 'El tipo de documento seleccionado no es válido.',

            // Número de documento
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.unique' => 'Ya existe una persona registrada con este número de documento.',
            'numero_documento.max' => 'El número de documento no puede exceder los 191 caracteres.',

            // Nombres
            'primer_nombre.required' => 'El primer nombre es obligatorio.',
            'primer_nombre.max' => 'El primer nombre no puede exceder los 191 caracteres.',
            'segundo_nombre.max' => 'El segundo nombre no puede exceder los 191 caracteres.',
            'primer_apellido.required' => 'El primer apellido es obligatorio.',
            'primer_apellido.max' => 'El primer apellido no puede exceder los 191 caracteres.',
            'segundo_apellido.max' => 'El segundo apellido no puede exceder los 191 caracteres.',

            // Fecha de nacimiento
            'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a la fecha actual.',

            // Género
            'genero_id.exists' => 'El género seleccionado no es válido.',

            // Contacto
            'celular.max' => 'El número de celular no puede exceder los 191 caracteres.',
            'telefono.max' => 'El número de teléfono no puede exceder los 191 caracteres.',
            'email.email' => 'El correo electrónico debe tener un formato válido.',
            'email.unique' => 'Ya existe una persona registrada con este correo electrónico.',
            'email.max' => 'El correo electrónico no puede exceder los 191 caracteres.',

            // Ubicación
            'pais_id.exists' => 'El país seleccionado no es válido.',
            'departamento_id.exists' => 'El departamento seleccionado no es válido.',
            'municipio_id.exists' => 'El municipio seleccionado no es válido.',
            'direccion.max' => 'La dirección no puede exceder los 191 caracteres.',

            // Caracterizaciones
            'caracterizaciones.array' => 'Las caracterizaciones deben ser una lista válida.',
            'caracterizaciones.*.exists' => 'Una o más caracterizaciones seleccionadas no existen.',

            // Observaciones
            'observaciones.max' => 'Las observaciones no pueden exceder los 500 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Mapear 'tipo_documento' a 'tipo_documento_id' si viene del formulario
        if ($this->has('tipo_documento') && !$this->has('tipo_documento_id')) {
            $this->merge(['tipo_documento_id' => $this->tipo_documento]);
        }

        // Sanitizar campos de texto
        $this->trimFields(['numero_documento', 'primer_nombre', 'primer_apellido']);
        $this->trimFields(['segundo_nombre', 'segundo_apellido', 'direccion', 'observaciones'], true);
        $this->trimField('email', true, fn (string $value): string => strtolower(trim($value)));
        if ($this->has('caracterizacion_ids') && !$this->has('caracterizaciones')) {
            $this->merge(['caracterizaciones' => $this->caracterizacion_ids ?? []]);
        }

        if ($this->has('genero') && !$this->has('genero_id')) {
            $this->merge(['genero_id' => $this->genero]);
        }
    }

    /**
     * Trim multiple fields according to the provided configuration.
     *
     * @param array<int, string> $fields
     * @param bool $skipNull
     */
    private function trimFields(array $fields, bool $skipNull = false): void
    {
        foreach ($fields as $field) {
            $this->trimField($field, $skipNull);
        }
    }

    /**
     * Trim and merge an individual field when it is present in the request.
     */
    private function trimField(string $field, bool $skipNull = false, ?callable $transform = null): void
    {
        if (!$this->has($field)) {
            return;
        }

        $value = $this->{$field};

        if ($value === null && $skipNull) {
            return;
        }

        $formatted = $transform ? $transform((string) $value) : trim((string) $value);

        $this->merge([$field => $formatted]);
    }
}
