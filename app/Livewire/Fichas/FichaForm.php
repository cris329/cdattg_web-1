<?php

namespace App\Livewire\Fichas;

use Livewire\Component;
use App\Models\FichaCaracterizacion;
use App\Models\ProgramaFormacion;
use App\Models\Sede;
use App\Models\Instructor;
use App\Models\Ambiente;
use App\Models\Regional;
use App\Services\FichaService;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

class FichaForm extends Component
{
    public $ficha;
    public $isEdit = false;
    
    // Datos del formulario
    public $ficha_codigo;
    public $programa_formacion_id;
    public $sede_id;
    public $instructor_id;
    public $ambiente_id;
    public $fecha_inicio;
    public $fecha_fin;
    public $modalidad_formacion_id;
    public $jornada_id;
    public $total_horas;
    public $dias_formacion = [];
    public $status = 1;
    
    // Listas para selects
    public $programas;
    public $sedes;
    public $instructores;
    public $ambientes;
    public $modalidades;
    public $jornadas;
    
    protected $listeners = [
        'refreshComponent' => '$refresh',
    ];

    public function mount($ficha = null, $isEdit = false)
    {
        $this->isEdit = $isEdit;
        
        if ($ficha && $isEdit) {
            $this->ficha = $ficha;
            $this->loadFichaData();
        }
        
        $this->cargarDatosSelects();
    }

    private function cargarDatosSelects()
    {
        // Programas de formación disponibles
        $this->programas = ProgramaFormacion::with('redConocimiento.regional')
            ->orderBy('nombre')
            ->get();
        
        // Sedes disponibles
        $this->sedes = Sede::with('regional')
            ->orderBy('sede')
            ->get();
        
        // Instructores disponibles
        $this->instructores = Instructor::with('persona')
            ->whereHas('persona', function ($query) {
                $query->where('status', 1);
            })
            ->orderBy('id', 'desc')
            ->get();
        
        // Ambientes disponibles
        $this->ambientes = Ambiente::where('status', 1)
            ->orderBy('title')
            ->get();
        
        // Modalidades de formación
        $this->modalidades = \App\Models\Parametro::whereHas('parametrosTemas', function($query) {
            $query->where('tema_id', 5);
        })->orderBy('name', 'asc')->get(); // MODALIDADES DE FORMACION (tema_id = 5)
        
        // Jornadas de formación
        $this->jornadas = \App\Models\ParametroTema::whereHas('tema', function($q) {
            $q->where('name', 'LIKE', '%JORNADAS%');
        })->whereHas('parametro', function($query) {
            $query->where('status', true);
        })->where('status', true)
          ->with('parametro')
          ->get()
          ->sortBy(function($pt) {
              return $pt->parametro->name;
          });
    }

    private function loadFichaData()
    {
        if ($this->ficha) {
            $this->ficha_codigo = $this->ficha->ficha;
            $this->programa_formacion_id = $this->ficha->programa_formacion_id;
            $this->sede_id = $this->ficha->sede_id;
            $this->instructor_id = $this->ficha->instructor_id;
            $this->ambiente_id = $this->ficha->ambiente_id;
            
            // Formatear fechas para inputs HTML
            $this->fecha_inicio = $this->ficha->fecha_inicio ? $this->ficha->fecha_inicio->format('Y-m-d') : null;
            $this->fecha_fin = $this->ficha->fecha_fin ? $this->ficha->fecha_fin->format('Y-m-d') : null;
            
            $this->modalidad_formacion_id = $this->ficha->modalidad_formacion_id;
            $this->jornada_id = $this->ficha->jornada_id;
            $this->total_horas = $this->ficha->total_horas;
            
            // Cargar días de formación desde la tabla pivote
            $diasFormacion = \App\Models\FichaDiasFormacion::where('ficha_id', $this->ficha->id)
                ->pluck('dia_id')
                ->toArray();
            $this->dias_formacion = $diasFormacion;
            
            $this->status = $this->ficha->status;
        }
    }

    public function updatedProgramaFormacionId()
    {
        // Cuando se cambia el programa, se puede filtrar instructores por especialidad
        // si se requiere en el futuro
    }

    public function updatedSedeId()
    {
        // Cuando se cambia la sede, se puede filtrar ambientes por sede
        // si se requiere en el futuro
    }

    public function save()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            $data = [
                'ficha' => $this->ficha_codigo,
                'programa_formacion_id' => $this->programa_formacion_id,
                'sede_id' => $this->sede_id,
                'instructor_id' => $this->instructor_id,
                'ambiente_id' => $this->ambiente_id,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin,
                'modalidad_formacion_id' => $this->modalidad_formacion_id,
                'jornada_id' => $this->jornada_id,
                'total_horas' => $this->total_horas,
                'status' => $this->status,
                'user_create_id' => auth()->id(),
                'user_edit_id' => auth()->id(),
            ];
            
            if ($this->isEdit && $this->ficha) {
                $this->ficha->update($data);
                
                // Actualizar días de formación
                \App\Models\FichaDiasFormacion::where('ficha_id', $this->ficha->id)->delete();
                foreach ($this->dias_formacion as $diaId) {
                    \App\Models\FichaDiasFormacion::create([
                        'ficha_id' => $this->ficha->id,
                        'dia_id' => (int) $diaId,
                    ]);
                }
                
                $message = 'Ficha actualizada exitosamente';
                $this->dispatch('fichaActualizada');
            } else {
                // Verificar que no exista una ficha con el mismo código
                $existingFicha = FichaCaracterizacion::where('ficha', $this->ficha_codigo)->first();
                if ($existingFicha) {
                    $this->dispatch('notify', ['type' => 'error', 'message' => 'Ya existe una ficha con este código']);
                    return;
                }
                
                $ficha = FichaCaracterizacion::create($data);
                
                // Guardar días de formación
                foreach ($this->dias_formacion as $diaId) {
                    \App\Models\FichaDiasFormacion::create([
                        'ficha_id' => $ficha->id,
                        'dia_id' => (int) $diaId,
                    ]);
                }
                
                $message = 'Ficha creada exitosamente';
                $this->dispatch('fichaCreada');
            }
            
            DB::commit();
            
            $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
            $this->dispatch('closeModal');
            
            // Limpiar formulario si es creación
            if (!$this->isEdit) {
                $this->resetForm();
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error al guardar la ficha: ' . $e->getMessage()]);
        }
    }

    public function resetForm()
    {
        $this->reset([
            'ficha_codigo',
            'programa_formacion_id',
            'sede_id',
            'instructor_id',
            'ambiente_id',
            'fecha_inicio',
            'fecha_fin',
            'modalidad_formacion_id',
            'jornada_id',
            'total_horas',
            'dias_formacion',
            'status',
        ]);
        
        $this->status = 1;
        $this->dias_formacion = [];
    }

    public function rules()
    {
        $rules = [
            'ficha_codigo' => 'required|string|max:20|unique:fichas_caracterizacion,ficha,' . ($this->ficha->id ?? 'null'),
            'programa_formacion_id' => 'required|exists:programas_formacion,id',
            'sede_id' => 'required|exists:sedes,id',
            'instructor_id' => 'required|exists:instructors,id',
            'ambiente_id' => 'required|exists:ambientes,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'modalidad_formacion_id' => 'required|exists:parametros,id',
            'jornada_id' => 'required|exists:parametros_temas,id',
            'total_horas' => 'required|integer|min:1|max:9999',
            'dias_formacion' => 'required|array|min:1',
            'dias_formacion.*' => 'exists:parametros,id',
            'status' => 'required|boolean',
        ];
        
        return $rules;
    }

    public function messages()
    {
        return [
            'ficha_codigo.required' => 'El código de la ficha es obligatorio',
            'ficha_codigo.unique' => 'Ya existe una ficha con este código',
            'programa_formacion_id.required' => 'Debe seleccionar un programa de formación',
            'programa_formacion_id.exists' => 'El programa de formación seleccionado no es válido',
            'sede_id.required' => 'Debe seleccionar una sede',
            'sede_id.exists' => 'La sede seleccionada no es válida',
            'instructor_id.required' => 'Debe seleccionar un instructor',
            'instructor_id.exists' => 'El instructor seleccionado no es válido',
            'ambiente_id.required' => 'Debe seleccionar un ambiente',
            'ambiente_id.exists' => 'El ambiente seleccionado no es válido',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida',
            'fecha_fin.required' => 'La fecha de fin es obligatoria',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio',
            'modalidad_formacion_id.required' => 'Debe seleccionar una modalidad de formación',
            'modalidad_formacion_id.exists' => 'La modalidad de formación seleccionada no es válida',
            'jornada_id.required' => 'Debe seleccionar una jornada de formación',
            'jornada_id.exists' => 'La jornada de formación seleccionada no es válida',
            'total_horas.required' => 'El total de horas es obligatorio',
            'total_horas.integer' => 'El total de horas debe ser un número entero',
            'total_horas.min' => 'El total de horas debe ser al menos 1',
            'total_horas.max' => 'El total de horas no puede exceder 9999',
            'dias_formacion.required' => 'Debe seleccionar al menos un día de formación',
            'dias_formacion.array' => 'Los días de formación deben ser un arreglo',
            'dias_formacion.min' => 'Debe seleccionar al menos un día de formación',
            'dias_formacion.*.exists' => 'El día de formación seleccionado no es válido',
            'status.required' => 'El estado es obligatorio',
            'status.boolean' => 'El estado debe ser verdadero o falso',
        ];
    }

    public function closeModal()
    {
        $this->dispatch('closeModal');
    }

    public function render()
    {
        return view('livewire.fichas.ficha-form');
    }

    #[On('showNotification')]
    public function showNotification($type, $message)
    {
        // Este método es para el sistema de notificaciones
        // El JavaScript manejará la visualización
    }
}
