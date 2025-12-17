<?php

namespace App\Http\Requests\Complementarios;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgramaComplementarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $programaId = $this->route('programa')?->id ?? $this->route('id');

        return [
            'codigo' => 'required|string|unique:complementarios_ofertados,codigo,' . $programaId,
            'nombre' => 'required|string',
            'justificacion' => 'required|string|max:600',
            'requisitos_ingreso' => 'required|string|max:400',
            'duracion' => 'required|integer|min:1',
            'cupos' => 'required|integer|min:1',
            'estado' => 'required|integer|in:0,1,2',
            'modalidad_id' => 'required|exists:parametros_temas,id',
            'jornada_id' => 'required|exists:jornadas_formacion,id',
            'ambiente_id' => 'required|exists:ambientes,id',
            'ambiente_comentario' => 'nullable|string|max:500',
            'dias' => 'nullable|array',
            'dias.*.dia_id' => 'required_with:dias|integer|exists:parametros_temas,id',
            'dias.*.hora_inicio' => 'required_with:dias.*.dia_id|date_format:H:i',
            'dias.*.hora_fin' => 'required_with:dias.*.dia_id|date_format:H:i|after:dias.*.hora_inicio',
            'competencias' => 'nullable|array',
            'competencias.*' => 'exists:competencias,id',
            'raps' => 'nullable|array',
            'raps.*' => 'exists:resultados_aprendizajes,id',
            'guias' => 'nullable|array',
            'guias.*' => 'exists:guia_aprendizajes,id',
        ];
    }

    /**
     * Obtiene los mensajes de validación personalizados.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'dias.array' => 'Los días de formación deben ser un array.',
            'dias.*.dia_id.required_with' => 'El día es requerido cuando se especifica un horario.',
            'dias.*.dia_id.integer' => 'El ID del día debe ser un número entero.',
            'dias.*.dia_id.exists' => 'El día seleccionado no existe en el sistema.',
            'dias.*.hora_inicio.required_with' => 'La hora de inicio es requerida cuando se especifica un día.',
            'dias.*.hora_inicio.date_format' => 'La hora de inicio debe tener el formato HH:MM (ej: 08:00).',
            'dias.*.hora_fin.required_with' => 'La hora de fin es requerida cuando se especifica un día.',
            'dias.*.hora_fin.date_format' => 'La hora de fin debe tener el formato HH:MM (ej: 16:00).',
            'dias.*.hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ];
    }

    /**
     * Obtiene los datos validados y normalizados.
     *
     * @param string|null $key
     * @param mixed $default
     * @return array<string, mixed>|mixed
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        if (isset($validated['dias']) && is_array($validated['dias'])) {
            $validated['dias'] = collect($validated['dias'])
                ->filter(static function ($dia) {
                    // Filtrar solo días que tengan dia_id y horarios válidos
                    return isset($dia['dia_id']) 
                        && isset($dia['hora_inicio']) 
                        && isset($dia['hora_fin'])
                        && !empty($dia['hora_inicio'])
                        && !empty($dia['hora_fin']);
                })
                ->map(static function ($dia) {
                    return [
                        'dia_id' => (int) $dia['dia_id'],
                        'hora_inicio' => $dia['hora_inicio'],
                        'hora_fin' => $dia['hora_fin'],
                    ];
                })
                ->unique('dia_id') // Evitar duplicados
                ->values()
                ->all();
        }

        return $validated;
    }
}

