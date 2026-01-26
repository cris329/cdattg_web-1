<?php

namespace App\Livewire\GuiasAprendizaje;

use Livewire\Component;
use App\Models\GuiasAprendizaje;
use App\Models\ResultadosAprendizaje;
use App\Models\ProgramaFormacion;
use Livewire\Attributes\Validate;
use Illuminate\Support\Str;

class GuiaAprendizajeForm extends Component
{
    public $guia;
    public $isEdit = false;
    
    #[Validate('required|string|max:20|unique:guia_aprendizajes,codigo')]
    public $codigo = '';
    
    #[Validate('required|string|max:255')]
    public $nombre = '';
    
    #[Validate('required|string|max:1000')]
    public $descripcion = '';
    
    #[Validate('required|exists:programas_formacion,id')]
    public $programa_formacion_id = '';
    
    #[Validate('required|integer|min:1|max:999')]
    public $duracion_horas = 40;
    
    #[Validate('required|integer|min:1|max:12')]
    public $duracion_meses = 1;
    
    #[Validate('nullable|string|max:500')]
    public $objetivo_general = '';
    
    #[Validate('nullable|string|max:1000')]
    public $metodologia = '';
    
    #[Validate('nullable|string|max:1000')]
    public $evaluacion = '';
    
    #[Validate('boolean')]
    public $status = true;
    
    // Para relaciones
    public $resultadosSeleccionados = [];
    public $resultadosDisponibles = [];
    public $searchResultado = '';
    
    protected $listeners = [
        'closeModal' => 'handleCloseModal',
        'refreshComponent' => '$refresh',
    ];

    public function mount($guia = null)
    {
        if ($guia) {
            $this->guia = $guia;
            $this->isEdit = true;
            $this->loadGuiaData();
        }
        
        $this->cargarResultadosDisponibles();
    }

    private function loadGuiaData()
    {
        if (!$this->guia) return;
        
        $this->codigo = $this->guia->codigo;
        $this->nombre = $this->guia->nombre;
        $this->descripcion = $this->guia->descripcion;
        $this->programa_formacion_id = $this->guia->programa_formacion_id;
        $this->duracion_horas = $this->guia->duracion_horas;
        $this->duracion_meses = $this->guia->duracion_meses;
        $this->objetivo_general = $this->guia->objetivo_general;
        $this->metodologia = $this->guia->metodologia;
        $this->evaluacion = $this->guia->evaluacion;
        $this->status = $this->guia->status;
        
        // Cargar resultados asociados
        $this->resultadosSeleccionados = $this->guia->resultadosAprendizaje()->pluck('resultados_aprendizajes.id')->toArray();
    }

    private function cargarResultadosDisponibles()
    {
        $query = ResultadosAprendizaje::orderBy('codigo');
        
        // Si estamos editando, excluir los ya seleccionados
        if ($this->isEdit && !empty($this->resultadosSeleccionados)) {
            $query->whereNotIn('id', $this->resultadosSeleccionados);
        }
        
        if ($this->searchResultado) {
            $query->where(function ($q) {
                $q->where('codigo', 'like', '%' . $this->searchResultado . '%')
                  ->orWhere('nombre', 'like', '%' . $this->searchResultado . '%');
            });
        }
        
        $this->resultadosDisponibles = $query->get();
    }

    public function updatedSearchResultado()
    {
        $this->cargarResultadosDisponibles();
    }

    public function agregarResultado($resultadoId)
    {
        if (!in_array($resultadoId, $this->resultadosSeleccionados)) {
            $this->resultadosSeleccionados[] = $resultadoId;
            $this->cargarResultadosDisponibles();
        }
    }

    public function quitarResultado($resultadoId)
    {
        $key = array_search($resultadoId, $this->resultadosSeleccionados);
        if ($key !== false) {
            unset($this->resultadosSeleccionados[$key]);
            $this->resultadosSeleccionados = array_values($this->resultadosSeleccionados);
            $this->cargarResultadosDisponibles();
        }
    }

    public function save()
    {
        // Validación condicional para el código
        $rules = [
            'codigo' => 'required|string|max:20|unique:guia_aprendizajes,codigo' . ($this->isEdit ? ',' . $this->guia->id : ''),
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:1000',
            'programa_formacion_id' => 'required|exists:programas_formacion,id',
            'duracion_horas' => 'required|integer|min:1|max:999',
            'duracion_meses' => 'required|integer|min:1|max:12',
            'objetivo_general' => 'nullable|string|max:500',
            'metodologia' => 'nullable|string|max:1000',
            'evaluacion' => 'nullable|string|max:1000',
            'status' => 'boolean',
        ];
        
        $this->validate($rules);
        
        try {
            \DB::beginTransaction();
            
            if ($this->isEdit) {
                // Actualizar guía existente
                $this->guia->update([
                    'codigo' => $this->codigo,
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'programa_formacion_id' => $this->programa_formacion_id,
                    'duracion_horas' => $this->duracion_horas,
                    'duracion_meses' => $this->duracion_meses,
                    'objetivo_general' => $this->objetivo_general,
                    'metodologia' => $this->metodologia,
                    'evaluacion' => $this->evaluacion,
                    'status' => $this->status,
                    'user_edit_id' => auth()->id(),
                ]);
                
                // Actualizar relaciones
                $this->guia->resultadosAprendizaje()->sync($this->resultadosSeleccionados);
                
                $message = "Guía de aprendizaje '{$this->codigo}' actualizada correctamente";
                $this->dispatch('guiaActualizada');
                
            } else {
                // Crear nueva guía
                $guia = GuiasAprendizaje::create([
                    'codigo' => $this->codigo,
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'programa_formacion_id' => $this->programa_formacion_id,
                    'duracion_horas' => $this->duracion_horas,
                    'duracion_meses' => $this->duracion_meses,
                    'objetivo_general' => $this->objetivo_general,
                    'metodologia' => $this->metodologia,
                    'evaluacion' => $this->evaluacion,
                    'status' => $this->status,
                    'user_create_id' => auth()->id(),
                ]);
                
                // Asociar resultados
                if (!empty($this->resultadosSeleccionados)) {
                    $guia->resultadosAprendizaje()->attach($this->resultadosSeleccionados);
                }
                
                $message = "Guía de aprendizaje '{$this->codigo}' creada correctamente";
                $this->dispatch('guiaCreada');
            }
            
            \DB::commit();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $message
            ]);
            
            // Cerrar modal
            $this->dispatch('closeModal');
            
        } catch (\Exception $e) {
            \DB::rollBack();
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al guardar la guía: ' . $e->getMessage()
            ]);
        }
    }

    public function handleCloseModal()
    {
        // Limpiar formulario
        $this->reset([
            'codigo', 'nombre', 'descripcion', 'programa_formacion_id',
            'duracion_horas', 'duracion_meses', 'objetivo_general',
            'metodologia', 'evaluacion', 'status'
        ]);
        $this->resultadosSeleccionados = [];
        $this->searchResultado = '';
        $this->cargarResultadosDisponibles();
    }

    public function render()
    {
        $programas = ProgramaFormacion::orderBy('nombre')->get();
        $resultadosAprendizaje = ResultadosAprendizaje::orderBy('codigo')->get();
        
        return view('livewire.guias-aprendizaje.guia-aprendizaje-form', compact(
            'programas',
            'resultadosAprendizaje'
        ));
    }
}
