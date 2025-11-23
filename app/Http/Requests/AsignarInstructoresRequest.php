<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Instructor;
use App\Models\FichaCaracterizacion;
use App\Models\InstructorFichaCaracterizacion;
use App\Models\Parametro;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AsignarInstructoresRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('EDITAR INSTRUCTOR') || $this->user()->can('CREAR INSTRUCTOR');
    }

    /**
     * Preparar datos antes de validación
     */
    protected function prepareForValidation(): void
    {
        // Si no se proporciona instructor_principal_id, obtenerlo de la ficha
        if (!$this->has('instructor_principal_id') || !$this->input('instructor_principal_id')) {
            $fichaId = $this->route('id');
            $ficha = FichaCaracterizacion::find($fichaId);
            
            if ($ficha && $ficha->instructor_id) {
                $this->merge([
                    'instructor_principal_id' => $ficha->instructor_id
                ]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'instructores' => 'required|array|min:1|max:10',
            'instructores.*.instructor_id' => [
                'required',
                'integer',
                'exists:instructors,id',
                function ($attribute, $value, $fail) {
                    $this->validarInstructorActivo($value, $fail);
                    $this->validarLimiteFichasActivas($value, $fail);
                }
            ],
            'instructores.*.fecha_inicio' => [
                'required',
                'date',
                'after_or_equal:today',
                function ($attribute, $value, $fail) {
                    $this->validarFechaInicioFicha($value, $fail);
                }
            ],
            'instructores.*.fecha_fin' => [
                'required',
                'date',
                'after_or_equal:instructores.*.fecha_inicio',
                function ($attribute, $value, $fail) {
                    $this->validarFechaFinFicha($value, $fail);
                }
            ],
            'instructores.*.total_horas_instructor' => 'nullable|integer|min:1|max:1000',
            'instructores.*.competencia_id' => [
                'nullable',
                'integer',
                'exists:competencias,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $this->validarCompetenciaPerteneceAPrograma($value, $fail, $attribute);
                    }
                }
            ],
            'instructores.*.resultados_aprendizaje' => 'nullable|array',
            'instructores.*.resultados_aprendizaje.*' => [
                'required',
                'integer',
                'exists:resultados_aprendizajes,id',
                function ($attribute, $value, $fail) {
                    $this->validarResultadoPerteneceACompetencia($value, $fail, $attribute);
                }
            ],
            // Validación principal: array simple de IDs de días
            'instructores.*.dias_semana' => 'required|array|min:1|max:7',
            'instructores.*.dias_semana.*' => 'required|integer|exists:parametros_temas,id',
            // Validación de días con horarios específicos (formato alternativo para modal)
            'instructores.*.dias' => 'nullable|array',
            'instructores.*.dias.*.hora_inicio' => 'required_with:instructores.*.dias|date_format:H:i',
            'instructores.*.dias.*.hora_fin' => 'required_with:instructores.*.dias|date_format:H:i|after:instructores.*.dias.*.hora_inicio',
            // Soporte para formato antiguo
            'instructores.*.dias_formacion' => 'nullable|array|min:1|max:7',
            'instructores.*.dias_formacion.*.dia_id' => 'exists:parametros_temas,id',
            'instructor_principal_id' => [
                'nullable',
                'integer',
                'exists:instructors,id'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'instructores.required' => 'Debe seleccionar al menos un instructor.',
            'instructores.min' => 'Debe seleccionar al menos un instructor.',
            'instructores.max' => 'No se pueden asignar más de 10 instructores a una ficha.',
            'instructores.*.instructor_id.required' => 'Debe seleccionar un instructor.',
            'instructores.*.instructor_id.exists' => 'El instructor seleccionado no existe.',
            'instructores.*.fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'instructores.*.fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'instructores.*.fecha_inicio.after_or_equal' => 'La fecha de inicio no puede ser anterior a hoy.',
            'instructores.*.fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'instructores.*.fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'instructores.*.fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
            'instructores.*.total_horas_instructor.integer' => 'Las horas totales deben ser un número entero.',
            'instructores.*.total_horas_instructor.min' => 'Las horas totales deben ser al menos 1.',
            'instructores.*.total_horas_instructor.max' => 'Las horas totales no pueden exceder 1000.',
            // Mensajes para días_semana (formato principal)
            'instructores.*.dias_semana.required' => 'Debe seleccionar al menos un día de formación.',
            'instructores.*.dias_semana.array' => 'Los días de formación deben ser una lista válida.',
            'instructores.*.dias_semana.min' => 'Debe seleccionar al menos un día de formación.',
            'instructores.*.dias_semana.max' => 'No se pueden asignar más de 7 días de formación.',
            'instructores.*.dias_semana.*.required' => 'El día seleccionado no es válido.',
            'instructores.*.dias_semana.*.integer' => 'El ID del día debe ser un número.',
            'instructores.*.dias_semana.*.exists' => 'El día seleccionado no existe en el sistema.',
            // Mensajes para días con horarios (formato alternativo)
            'instructores.*.dias.*.hora_inicio.required_with' => 'La hora de inicio es obligatoria cuando se selecciona un día.',
            'instructores.*.dias.*.hora_inicio.date_format' => 'El formato de la hora de inicio debe ser HH:MM.',
            'instructores.*.dias.*.hora_fin.required_with' => 'La hora de fin es obligatoria cuando se selecciona un día.',
            'instructores.*.dias.*.hora_fin.date_format' => 'El formato de la hora de fin debe ser HH:MM.',
            'instructores.*.dias.*.hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            // Mensajes para formato antiguo
            'instructores.*.dias_formacion.min' => 'Debe seleccionar al menos un día de formación.',
            'instructores.*.dias_formacion.max' => 'No se pueden asignar más de 7 días de formación.',
            'instructores.*.dias_formacion.*.dia_id.exists' => 'El día seleccionado no existe.',
            'instructor_principal_id.exists' => 'El instructor líder seleccionado no existe en el sistema.',
            'instructor_principal_id.integer' => 'El instructor líder debe ser un identificador válido.'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validarConflictosFechas($validator);
            $this->validarEspecialidadesRequeridas($validator);
            $this->validarDisponibilidadHoraria($validator);
            $this->validarReglasSENA($validator);
            $this->validarCoherenciaHorasCompetencia($validator);
            
            // Las sugerencias han sido removidas por solicitud del usuario
        });
    }

    /**
     * Validar que el instructor esté activo
     */
    private function validarInstructorActivo($instructorId, $fail): void
    {
        $instructor = Instructor::find($instructorId);
        if ($instructor && !$instructor->status) {
            $fail("El instructor {$instructor->nombre_completo} está inactivo.");
        }
    }

    /**
     * Validar límite de fichas activas por instructor
     */
    private function validarLimiteFichasActivas($instructorId, $fail): void
    {
        $instructor = Instructor::find($instructorId);
        if (!$instructor) return;

        $fichasActivas = $instructor->instructorFichas()
            ->whereHas('ficha', function($q) {
                $q->where('status', true)
                  ->where('fecha_fin', '>=', now()->toDateString());
            })
            ->count();

        if ($fichasActivas >= 5) { // Máximo 5 fichas activas según reglas SENA
            $fail("El instructor {$instructor->nombre_completo} ya tiene el máximo de fichas activas (5).");
        }
    }

    /**
     * Validar que la fecha de inicio no sea anterior a la fecha de inicio de la ficha
     */
    private function validarFechaInicioFicha($fechaInicio, $fail): void
    {
        $fichaId = $this->route('id');
        $ficha = FichaCaracterizacion::find($fichaId);
        
        if ($ficha && $ficha->fecha_inicio) {
            $fechaInicioFicha = Carbon::parse($ficha->fecha_inicio);
            $fechaInicioInstructor = Carbon::parse($fechaInicio);
            
            if ($fechaInicioInstructor->lt($fechaInicioFicha)) {
                $fail("La fecha de inicio del instructor debe ser posterior o igual a la fecha de inicio de la ficha ({$fechaInicioFicha->format('d/m/Y')}).");
            }
        }
    }

    /**
     * Validar que la fecha de fin no sea posterior a la fecha de fin de la ficha
     */
    private function validarFechaFinFicha($fechaFin, $fail): void
    {
        $fichaId = $this->route('id');
        $ficha = FichaCaracterizacion::find($fichaId);
        
        if ($ficha && $ficha->fecha_fin) {
            $fechaFinFicha = Carbon::parse($ficha->fecha_fin);
            $fechaFinInstructor = Carbon::parse($fechaFin);
            
            if ($fechaFinInstructor->gt($fechaFinFicha)) {
                $fail("La fecha de fin del instructor debe ser anterior o igual a la fecha de fin de la ficha ({$fechaFinFicha->format('d/m/Y')}).");
            }
        }
    }

    /**
     * Validar que el instructor principal esté en la lista de instructores
     * NOTA: Esta validación está deshabilitada porque el instructor principal
     * es el líder de la ficha asignado en la creación, no necesariamente
     * tiene que estar en la lista de instructores adicionales.
     */
    private function validarInstructorPrincipalEnLista($instructorPrincipalId, $fail): void
    {
        // Validación deshabilitada - El instructor principal puede ser independiente
        // de los instructores adicionales asignados
        return;
    }

    /**
     * Validar conflictos de fechas entre instructores (considerando jornadas y días)
     */
    private function validarConflictosFechas($validator): void
    {
        $instructores = $this->input('instructores', []);
        $fichaId = $this->route('id');
        $ficha = FichaCaracterizacion::find($fichaId);
        $jornadaIdFicha = $ficha ? $ficha->jornada_id : null;

        foreach ($instructores as $index => $instructorData) {
            $instructorId = $instructorData['instructor_id'];
            $fechaInicio = Carbon::parse($instructorData['fecha_inicio']);
            $fechaFin = Carbon::parse($instructorData['fecha_fin']);
            
            // Extraer IDs de días según el formato proporcionado
            $diasNuevos = [];
            if (isset($instructorData['dias']) && is_array($instructorData['dias'])) {
                $diasNuevos = array_keys($instructorData['dias']); // Nuevo formato: ['12' => ['hora_inicio' => '08:00', ...]]
            } elseif (isset($instructorData['dias_semana']) && is_array($instructorData['dias_semana'])) {
                $diasNuevos = $instructorData['dias_semana']; // Array simple de IDs
            } elseif (isset($instructorData['dias_formacion']) && is_array($instructorData['dias_formacion'])) {
                $diasNuevos = collect($instructorData['dias_formacion'])->pluck('dia_id')->filter()->toArray(); // Formato antiguo
            }

            // 1. Verificar conflictos con otras fichas del mismo instructor
            $this->validarConflictosOtrosInstructor($validator, $instructorId, $fechaInicio, $fechaFin, $diasNuevos, $jornadaIdFicha, $index);

            // 2. Verificar conflictos con otros instructores en la MISMA ficha
            $this->validarConflictosMismaFicha($validator, $instructores, $index, $instructorId, $fechaInicio, $fechaFin, $diasNuevos);
        }
    }

    /**
     * Validar conflictos con otras fichas del mismo instructor
     * Valida: fechas, jornada, días y horarios
     */
    private function validarConflictosOtrosInstructor($validator, $instructorId, $fechaInicio, $fechaFin, $diasNuevos, $jornadaIdFicha, $index): void
    {
        $instructorData = $this->input("instructores.{$index}", []);
        
        // Extraer horarios si están disponibles (formato: dias[dia_id][hora_inicio/hora_fin])
        $horariosNuevos = [];
        if (isset($instructorData['dias']) && is_array($instructorData['dias'])) {
            foreach ($instructorData['dias'] as $diaId => $diaInfo) {
                if (isset($diaInfo['hora_inicio']) && isset($diaInfo['hora_fin'])) {
                    $horariosNuevos[$diaId] = [
                        'hora_inicio' => $diaInfo['hora_inicio'],
                        'hora_fin' => $diaInfo['hora_fin']
                    ];
                }
            }
        }
        
        $conflictosQuery = InstructorFichaCaracterizacion::where('instructor_id', $instructorId)
            ->whereHas('ficha', function($q) use ($jornadaIdFicha) {
                    $q->where('status', true);
                
                // Solo validar conflictos en la misma jornada
                if ($jornadaIdFicha) {
                    $q->where('jornada_id', $jornadaIdFicha);
                }
                })
                ->where(function($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                      ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                      ->orWhere(function($subQ) use ($fechaInicio, $fechaFin) {
                          $subQ->where('fecha_inicio', '<=', $fechaInicio)
                               ->where('fecha_fin', '>=', $fechaFin);
                      });
                })
            ->with(['ficha.jornadaFormacion.parametro', 'instructorFichaDias.dia']);

        $conflictosExistentes = $conflictosQuery->get();

        // Filtrar conflictos por días de la semana y horarios si se especifican
        if (!empty($diasNuevos)) {
            $conflictosExistentes = $conflictosExistentes->filter(function($conflicto) use ($diasNuevos, $horariosNuevos) {
                $diasExistentes = $conflicto->instructorFichaDias->pluck('dia_id')->toArray();
                $diasEnComun = array_intersect($diasNuevos, $diasExistentes);
                
                // Si no hay días en común, no hay conflicto
                if (empty($diasEnComun)) {
                    return false;
                }
                
                // Si hay horarios especificados, validar también conflictos de horario
                if (!empty($horariosNuevos)) {
                    foreach ($diasEnComun as $diaId) {
                        // Buscar horario del día en los nuevos horarios
                        if (!isset($horariosNuevos[$diaId])) {
                            continue; // Si no hay horario especificado para este día, no validar horario
                        }
                        
                        $horaInicioNueva = $horariosNuevos[$diaId]['hora_inicio'];
                        $horaFinNueva = $horariosNuevos[$diaId]['hora_fin'];
                        
                        // Buscar horario del día en las asignaciones existentes
                        $diaExistente = $conflicto->instructorFichaDias->firstWhere('dia_id', $diaId);
                        if ($diaExistente && $diaExistente->hora_inicio && $diaExistente->hora_fin) {
                            // Verificar si hay conflicto de horario
                            if ($this->hayConflictoHorario($horaInicioNueva, $horaFinNueva, $diaExistente->hora_inicio, $diaExistente->hora_fin)) {
                                return true; // Hay conflicto de horario
                            }
                        }
                    }
                    // Si hay días en común pero no hay conflictos de horario, no es conflicto
                    return false;
                }
                
                // Si hay días en común pero no se especificaron horarios, considerar conflicto
                return true;
            });
        }

        if ($conflictosExistentes->isNotEmpty()) {
            $instructor = Instructor::find($instructorId);
            $conflictosText = $conflictosExistentes->map(function($conflicto) use ($diasNuevos, $horariosNuevos) {
                    $programaNombre = $conflicto->ficha->programaFormacion->nombre ?? 'Sin programa';
                $jornada = $conflicto->ficha->jornadaFormacion->parametro->name ?? 'Sin jornada';
                
                // Mostrar días en conflicto
                $diasExistentes = $conflicto->instructorFichaDias->pluck('dia_id')->toArray();
                $diasEnComun = array_intersect($diasNuevos, $diasExistentes);
                $diasNombres = $conflicto->instructorFichaDias
                    ->whereIn('dia_id', $diasEnComun)
                    ->map(function($dia) use ($horariosNuevos) {
                        $nombre = $dia->dia->name ?? '';
                        $horario = '';
                        if (isset($horariosNuevos[$dia->dia_id])) {
                            $horario = " ({$horariosNuevos[$dia->dia_id]['hora_inicio']}-{$horariosNuevos[$dia->dia_id]['hora_fin']})";
                        }
                        if ($dia->hora_inicio && $dia->hora_fin) {
                            $horario .= " [Conflicto: {$dia->hora_inicio}-{$dia->hora_fin}]";
                        }
                        return $nombre . $horario;
                    })
                    ->filter()
                    ->implode(', ');
                
                $diasInfo = $diasNombres ? " - Días en conflicto: {$diasNombres}" : '';
                return "Ficha {$conflicto->ficha->ficha} ({$programaNombre}) - Jornada: {$jornada}{$diasInfo} del " . Carbon::parse($conflicto->fecha_inicio)->format('d/m/Y') . " al " . Carbon::parse($conflicto->fecha_fin)->format('d/m/Y');
                })->implode(', ');

                $validator->errors()->add(
                    "instructores.{$index}.fecha_inicio",
                "📅 El instructor {$instructor->nombre_completo} ya tiene fichas con fechas superpuestas en la misma jornada, días y horarios: {$conflictosText}. Ajuste las fechas, jornada, días u horarios para evitar conflictos."
            );
        }
    }
    
    /**
     * Verificar si hay conflicto entre dos rangos horarios
     */
    private function hayConflictoHorario(string $inicio1, string $fin1, string $inicio2, string $fin2): bool
    {
        $inicio1 = Carbon::parse($inicio1);
        $fin1 = Carbon::parse($fin1);
        $inicio2 = Carbon::parse($inicio2);
        $fin2 = Carbon::parse($fin2);

        // Hay conflicto si los rangos se superponen
        return !($fin1->lte($inicio2) || $inicio1->gte($fin2));
    }

    /**
     * Validar conflictos entre instructores en la misma ficha
     */
    private function validarConflictosMismaFicha($validator, $instructores, $indexActual, $instructorIdActual, $fechaInicioActual, $fechaFinActual, $diasActuales): void
    {
        $fichaId = $this->route('id');
        
        \Log::info('🔍 VALIDACIÓN MISMA FICHA', [
            'instructores_total' => count($instructores),
            'index_actual' => $indexActual,
            'instructor_actual' => $instructorIdActual,
            'fecha_actual' => $fechaInicioActual->format('Y-m-d') . ' a ' . $fechaFinActual->format('Y-m-d'),
            'dias_actuales' => $diasActuales,
            'ficha_id' => $fichaId
        ]);

        // 1. Verificar conflictos con otros instructores en el mismo formulario
        foreach ($instructores as $indexOtro => $instructorOtro) {
            // No comparar consigo mismo
            if ($indexActual === $indexOtro) continue;

            $instructorIdOtro = $instructorOtro['instructor_id'];
            $fechaInicioOtro = Carbon::parse($instructorOtro['fecha_inicio']);
            $fechaFinOtro = Carbon::parse($instructorOtro['fecha_fin']);
            
            // Extraer IDs de días del otro instructor
            $diasOtros = [];
            if (isset($instructorOtro['dias']) && is_array($instructorOtro['dias'])) {
                $diasOtros = array_keys($instructorOtro['dias']);
            } elseif (isset($instructorOtro['dias_semana']) && is_array($instructorOtro['dias_semana'])) {
                $diasOtros = $instructorOtro['dias_semana'];
            } elseif (isset($instructorOtro['dias_formacion']) && is_array($instructorOtro['dias_formacion'])) {
                $diasOtros = collect($instructorOtro['dias_formacion'])->pluck('dia_id')->filter()->toArray();
            }

            \Log::info('🔍 COMPARANDO CON INSTRUCTOR EN FORMULARIO', [
                'index_otro' => $indexOtro,
                'instructor_otro' => $instructorIdOtro,
                'fecha_otro' => $fechaInicioOtro->format('Y-m-d') . ' a ' . $fechaFinOtro->format('Y-m-d'),
                'dias_otros' => $diasOtros
            ]);

            // Verificar si hay superposición de fechas
            $haySuperposicion = $this->haySuperposicionFechas($fechaInicioActual, $fechaFinActual, $fechaInicioOtro, $fechaFinOtro);
            
            \Log::info('🔍 SUPERPOSICIÓN DE FECHAS', [
                'hay_superposicion' => $haySuperposicion
            ]);
            
            if ($haySuperposicion) {
                // Verificar si hay días en común
                $diasEnComun = array_intersect($diasActuales, $diasOtros);
                
                \Log::info('🔍 DÍAS EN COMÚN', [
                    'dias_en_comun' => $diasEnComun,
                    'hay_conflicto' => !empty($diasEnComun)
                ]);
                
                if (!empty($diasEnComun)) {
                    $instructorActual = Instructor::find($instructorIdActual);
                    $instructorOtro = Instructor::find($instructorIdOtro);
                    
                    // Obtener nombres de los días en común desde Parametro
                    $diasNombres = Parametro::whereIn('id', $diasEnComun)->pluck('name')->implode(', ');
                    
                    \Log::error('❌ CONFLICTO DETECTADO EN FORMULARIO', [
                        'instructor_actual' => $instructorActual->nombre_completo,
                        'instructor_otro' => $instructorOtro->nombre_completo,
                        'dias_conflicto' => $diasNombres
                    ]);
                    
                    $validator->errors()->add(
                        "instructores.{$indexActual}.fecha_inicio",
                        "⚠️ CONFLICTO EN LA MISMA FICHA: El instructor {$instructorActual->nombre_completo} no puede ser asignado en las mismas fechas y días ({$diasNombres}) que el instructor {$instructorOtro->nombre_completo}. Ajuste las fechas o días para evitar el conflicto."
                    );
                }
            }
        }

        // 2. Verificar conflictos con instructores ya asignados en la ficha
        $this->validarConflictosConAsignacionesExistentes($validator, $instructorIdActual, $fechaInicioActual, $fechaFinActual, $diasActuales, $indexActual, $fichaId);
    }

    /**
     * Validar conflictos con instructores ya asignados en la ficha
     */
    private function validarConflictosConAsignacionesExistentes($validator, $instructorIdActual, $fechaInicioActual, $fechaFinActual, $diasActuales, $indexActual, $fichaId): void
    {
        // Obtener asignaciones existentes en la ficha (excluyendo el instructor actual si ya está asignado)
        $asignacionesExistentes = InstructorFichaCaracterizacion::where('ficha_id', $fichaId)
            ->where('instructor_id', '!=', $instructorIdActual) // Excluir el instructor actual
            ->with(['instructor.persona', 'instructorFichaDias.dia'])
            ->get();

        \Log::info('🔍 ASIGNACIONES EXISTENTES EN FICHA', [
            'ficha_id' => $fichaId,
            'total_existentes' => $asignacionesExistentes->count(),
            'asignaciones' => $asignacionesExistentes->map(function($a) {
                return [
                    'instructor_id' => $a->instructor_id,
                    'instructor_nombre' => $a->instructor->nombre_completo ?? 'Sin nombre',
                    'fecha_inicio' => $a->fecha_inicio,
                    'fecha_fin' => $a->fecha_fin,
                    'dias' => $a->instructorFichaDias->pluck('dia_id')->toArray()
                ];
            })->toArray()
        ]);

        foreach ($asignacionesExistentes as $asignacionExistente) {
            $instructorIdExistente = $asignacionExistente->instructor_id;
            $fechaInicioExistente = Carbon::parse($asignacionExistente->fecha_inicio);
            $fechaFinExistente = Carbon::parse($asignacionExistente->fecha_fin);
            $diasExistentes = $asignacionExistente->instructorFichaDias->pluck('dia_id')->toArray();

            \Log::info('🔍 COMPARANDO CON ASIGNACIÓN EXISTENTE', [
                'instructor_existente' => $instructorIdExistente,
                'fecha_existente' => $fechaInicioExistente->format('Y-m-d') . ' a ' . $fechaFinExistente->format('Y-m-d'),
                'dias_existentes' => $diasExistentes
            ]);

            // Verificar si hay superposición de fechas
            $haySuperposicion = $this->haySuperposicionFechas($fechaInicioActual, $fechaFinActual, $fechaInicioExistente, $fechaFinExistente);
            
            \Log::info('🔍 SUPERPOSICIÓN CON EXISTENTE', [
                'hay_superposicion' => $haySuperposicion
            ]);
            
            if ($haySuperposicion) {
                // Verificar si hay días en común
                $diasEnComun = array_intersect($diasActuales, $diasExistentes);
                
                \Log::info('🔍 DÍAS EN COMÚN CON EXISTENTE', [
                    'dias_en_comun' => $diasEnComun,
                    'hay_conflicto' => !empty($diasEnComun)
                ]);
                
                if (!empty($diasEnComun)) {
                    $instructorActual = Instructor::find($instructorIdActual);
                    $instructorExistente = Instructor::find($instructorIdExistente);
                    
                    // Obtener nombres de los días en común desde Parametro
                    $diasNombres = Parametro::whereIn('id', $diasEnComun)->pluck('name')->implode(', ');
                    
                    \Log::error('❌ CONFLICTO CON ASIGNACIÓN EXISTENTE', [
                        'instructor_actual' => $instructorActual->nombre_completo,
                        'instructor_existente' => $instructorExistente->nombre_completo,
                        'dias_conflicto' => $diasNombres
                    ]);
                    
                    $validator->errors()->add(
                        "instructores.{$indexActual}.fecha_inicio",
                        "⚠️ CONFLICTO CON INSTRUCTOR YA ASIGNADO: El instructor {$instructorActual->nombre_completo} no puede ser asignado en las mismas fechas y días ({$diasNombres}) que el instructor {$instructorExistente->nombre_completo} que ya está asignado a esta ficha. Ajuste las fechas o días para evitar el conflicto."
                    );
                }
            }
        }
    }

    /**
     * Verificar si dos rangos de fechas se superponen
     */
    private function haySuperposicionFechas($fechaInicio1, $fechaFin1, $fechaInicio2, $fechaFin2): bool
    {
        return $fechaInicio1->lte($fechaFin2) && $fechaFin1->gte($fechaInicio2);
    }

    /**
     * Validar especialidades requeridas
     * NOTA: Esta validación se maneja en InstructorBusinessRulesService para evitar duplicados
     */
    private function validarEspecialidadesRequeridas($validator): void
    {
        // La validación de especialidades se maneja en InstructorBusinessRulesService
        // para evitar duplicados con verificarDisponibilidad()
        return;
    }

    /**
     * Validar disponibilidad horaria (considerando jornadas y días de la semana)
     * NOTA: Esta validación ahora se maneja en validarConflictosFechas() para evitar duplicados
     */
    private function validarDisponibilidadHoraria($validator): void
    {
        // La validación de días y jornadas se maneja en validarConflictosFechas()
        // para evitar duplicados y tener una lógica centralizada
            return;
    }

    /**
     * Validar reglas específicas del SENA
     */
    private function validarReglasSENA($validator): void
    {
        $fichaId = $this->route('id');
        $ficha = FichaCaracterizacion::with('sede.regional')->find($fichaId);
        $instructores = $this->input('instructores', []);

        foreach ($instructores as $index => $instructorData) {
            $instructor = Instructor::find($instructorData['instructor_id']);
            if (!$instructor) continue;

            // Verificar que el instructor pertenezca a la misma regional
            $fichaRegionalId = $ficha && $ficha->sede ? $ficha->sede->regional_id : null;
            if ($ficha && $fichaRegionalId && $instructor->regional_id !== $fichaRegionalId) {
                $validator->errors()->add(
                    "instructores.{$index}.instructor_id",
                    "El instructor {$instructor->nombre_completo} debe pertenecer a la misma regional que la ficha."
                );
            }

            // Verificar experiencia mínima
            // NOTA: Esta validación se maneja en InstructorBusinessRulesService para evitar duplicados
            // if (($instructor->anos_experiencia ?? 0) < 1) {
            //     $validator->errors()->add(
            //         "instructores.{$index}.instructor_id",
            //         "👨‍🏫 El instructor {$instructor->nombre_completo} no cumple con la experiencia mínima requerida (1 año). Seleccione un instructor con más experiencia."
            //     );
            // }
        }
    }

    /**
     * Validar que la competencia pertenezca al programa de formación de la ficha
     */
    private function validarCompetenciaPerteneceAPrograma($competenciaId, $fail, $attribute): void
    {
        $fichaId = $this->route('id');
        $ficha = FichaCaracterizacion::with('programaFormacion.competencias')->find($fichaId);
        
        if (!$ficha || !$ficha->programaFormacion) {
            $fail("La ficha no tiene un programa de formación asociado.");
            return;
        }

        $competenciaPertenece = $ficha->programaFormacion->competencias->contains('id', $competenciaId);
        
        if (!$competenciaPertenece) {
            $competencia = \App\Models\Competencia::find($competenciaId);
            $competenciaNombre = $competencia ? $competencia->nombre : 'Competencia desconocida';
            $fail("La competencia '{$competenciaNombre}' no pertenece al programa de formación de esta ficha.");
        }
    }

    /**
     * Validar que el resultado de aprendizaje pertenezca a la competencia seleccionada
     */
    private function validarResultadoPerteneceACompetencia($resultadoId, $fail, $attribute): void
    {
        // Extraer el índice del instructor del atributo
        // Formato: instructores.0.resultados_aprendizaje.0
        preg_match('/instructores\.(\d+)\.resultados_aprendizaje\.\d+/', $attribute, $matches);
        $instructorIndex = $matches[1] ?? null;
        
        if ($instructorIndex === null) {
            return; // No se puede validar sin el índice
        }

        $instructorData = $this->input("instructores.{$instructorIndex}", []);
        $competenciaId = $instructorData['competencia_id'] ?? null;

        if (!$competenciaId) {
            $fail("Debe seleccionar una competencia antes de seleccionar resultados de aprendizaje.");
            return;
        }

        $competencia = \App\Models\Competencia::with('resultadosAprendizaje')->find($competenciaId);
        
        if (!$competencia) {
            $fail("La competencia seleccionada no existe.");
            return;
        }

        $resultadoPertenece = $competencia->resultadosAprendizaje->contains('id', $resultadoId);
        
        if (!$resultadoPertenece) {
            $resultado = \App\Models\ResultadosAprendizaje::find($resultadoId);
            $resultadoNombre = $resultado ? $resultado->nombre : 'Resultado desconocido';
            $fail("El resultado de aprendizaje '{$resultadoNombre}' no pertenece a la competencia seleccionada.");
        }
    }

    /**
     * Validar que las horas trabajadas sean coherentes con la duración de la competencia o resultados de aprendizaje
     */
    private function validarCoherenciaHorasCompetencia($validator): void
    {
        $instructores = $this->input('instructores', []);
        $fichaId = $this->route('id');
        $ficha = FichaCaracterizacion::with(['diasFormacion', 'jornadaFormacion.parametro'])->find($fichaId);
        
        if (!$ficha) {
            return;
        }

        foreach ($instructores as $index => $instructorData) {
            $competenciaId = $instructorData['competencia_id'] ?? null;
            $resultadosIds = $instructorData['resultados_aprendizaje'] ?? [];
            
            // Solo validar si tiene competencia o resultados asignados
            if (!$competenciaId && empty($resultadosIds)) {
                continue;
            }

            // Calcular horas totales que se trabajarán
            $horasTrabajadas = $this->calcularHorasTrabajadas($instructorData, $ficha);
            
            // Obtener duración esperada
            $duracionEsperada = 0;
            
            if (!empty($resultadosIds)) {
                // Si hay resultados asignados, sumar sus duraciones
                $resultados = \App\Models\ResultadosAprendizaje::whereIn('id', $resultadosIds)->get();
                $duracionEsperada = $resultados->sum('duracion');
            } elseif ($competenciaId) {
                // Si solo hay competencia, usar su duración
                $competencia = \App\Models\Competencia::find($competenciaId);
                if ($competencia) {
                    $duracionEsperada = $competencia->duracion;
                }
            }

            if ($duracionEsperada <= 0) {
                continue; // No se puede validar sin duración
            }

            // Calcular diferencia porcentual (margen de tolerancia del 10%)
            $diferencia = abs($horasTrabajadas - $duracionEsperada);
            $porcentajeDiferencia = ($diferencia / $duracionEsperada) * 100;
            
            // Si la diferencia es mayor al 10%, mostrar advertencia
            if ($porcentajeDiferencia > 10) {
                $competenciaNombre = $competenciaId 
                    ? (\App\Models\Competencia::find($competenciaId)->nombre ?? 'Competencia')
                    : 'Resultados de aprendizaje';
                
                $validator->errors()->add(
                    "instructores.{$index}.fecha_inicio",
                    "⚠️ INCOHERENCIA DE HORAS: Las horas trabajadas ({$horasTrabajadas}h) no son coherentes con la duración esperada ({$duracionEsperada}h) de {$competenciaNombre}. Diferencia: {$diferencia}h ({$porcentajeDiferencia}%). Ajuste las fechas, días u horarios para que coincidan."
                );
            }
        }
    }

    /**
     * Calcular horas totales trabajadas basándose en fechas, días y horarios
     */
    private function calcularHorasTrabajadas(array $instructorData, FichaCaracterizacion $ficha): int
    {
        try {
            $fechaInicio = Carbon::parse($instructorData['fecha_inicio']);
            $fechaFin = Carbon::parse($instructorData['fecha_fin']);
            
            // Obtener días seleccionados
            $diasSeleccionados = [];
            $diasConHorarios = [];
            
            if (isset($instructorData['dias']) && is_array($instructorData['dias'])) {
                // Formato con horarios específicos
                foreach ($instructorData['dias'] as $diaId => $diaInfo) {
                    $diasSeleccionados[] = $diaId;
                    if (isset($diaInfo['hora_inicio']) && isset($diaInfo['hora_fin'])) {
                        $diasConHorarios[$diaId] = [
                            'hora_inicio' => $diaInfo['hora_inicio'],
                            'hora_fin' => $diaInfo['hora_fin']
                        ];
                    }
                }
            } elseif (isset($instructorData['dias_semana']) && is_array($instructorData['dias_semana'])) {
                $diasSeleccionados = $instructorData['dias_semana'];
            } elseif (isset($instructorData['dias_formacion']) && is_array($instructorData['dias_formacion'])) {
                $diasSeleccionados = collect($instructorData['dias_formacion'])->pluck('dia_id')->filter()->toArray();
            }
            
            if (empty($diasSeleccionados)) {
                return 0;
            }

            // Preparar datos de días con horarios
            $diasParaCalculo = [];
            foreach ($diasSeleccionados as $diaId) {
                if (isset($diasConHorarios[$diaId])) {
                    // Usar horarios del formulario
                    $diasParaCalculo[] = [
                        'dia_id' => $diaId,
                        'hora_inicio' => $diasConHorarios[$diaId]['hora_inicio'],
                        'hora_fin' => $diasConHorarios[$diaId]['hora_fin']
                    ];
                } else {
                    // Buscar horario del día en la configuración de la ficha
                    $diaFormacionFicha = $ficha->diasFormacion->firstWhere('dia_id', $diaId);
                    $horaInicio = $diaFormacionFicha->hora_inicio ?? '08:00';
                    $horaFin = $diaFormacionFicha->hora_fin ?? '12:00';
                    
                    $diasParaCalculo[] = [
                        'dia_id' => $diaId,
                        'hora_inicio' => $horaInicio,
                        'hora_fin' => $horaFin
                    ];
                }
            }

            // Crear objeto temporal para el cálculo
            $instructorFichaTemp = new \stdClass();
            $instructorFichaTemp->fecha_inicio = $fechaInicio->format('Y-m-d');
            $instructorFichaTemp->fecha_fin = $fechaFin->format('Y-m-d');
            $instructorFichaTemp->ficha = $ficha;

            // Usar el servicio para generar fechas efectivas
            $diasService = app(\App\Services\InstructorFichaDiasService::class);
            $fechasEfectivas = $diasService->generarFechasEfectivas($instructorFichaTemp, $diasParaCalculo);
            
            // Calcular horas totales
            $totalHoras = 0;
            foreach ($fechasEfectivas as $fecha) {
                if ($fecha['hora_inicio'] && $fecha['hora_fin']) {
                    $horas = $this->convertirTiempoAHoras($fecha['hora_inicio'], $fecha['hora_fin']);
                    $totalHoras += $horas;
                }
            }
            
            return (int) round($totalHoras);
            
        } catch (\Exception $e) {
            \Log::error('Error calculando horas trabajadas en validación', [
                'error' => $e->getMessage(),
                'instructor_data' => $instructorData
            ]);
            return 0;
        }
    }

    /**
     * Convertir tiempo de inicio y fin a horas decimales
     */
    private function convertirTiempoAHoras(?string $horaInicio, ?string $horaFin): float
    {
        if (!$horaInicio || !$horaFin) {
            return 0;
        }

        try {
            $inicio = Carbon::parse($horaInicio);
            $fin = Carbon::parse($horaFin);
            
            // Si la hora fin es menor que inicio, asumir que es del día siguiente
            if ($fin->lt($inicio)) {
                $fin->addDay();
            }
            
            $diferencia = $inicio->diffInMinutes($fin);
            return $diferencia / 60; // Convertir minutos a horas
        } catch (\Exception $e) {
            return 0;
        }
    }

}
