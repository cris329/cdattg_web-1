<?php

namespace App\Livewire\Aprendices;

use Livewire\Component;
use App\Models\Aprendiz;
use App\Models\Persona;
use App\Models\FichaCaracterizacion;
use App\Models\ProgramaFormacion;
use App\Models\Regional;
use App\Models\Parametro;
use App\Models\ParametroTema;
use App\Services\AprendizService;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

class AprendizForm extends Component
{
    public $aprendiz;
    public $isEdit = false;
    
    // Datos del aprendiz (simplificado como create.blade.php)
    public $persona_id;
    public $ficha_caracterizacion_id;
    public $estado = 1;
    
    // Listas para selects
    public $fichas;
    public $personas; // Personas disponibles para ser aprendices
    
    protected $listeners = [
        'refreshComponent' => '$refresh',
    ];

    public function mount($aprendiz = null, $isEdit = false)
    {
        $this->isEdit = $isEdit;
        
        if ($aprendiz && $isEdit) {
            $this->aprendiz = $aprendiz;
            $this->loadAprendizData();
        }
        
        $this->cargarDatosSelects();
    }

    private function cargarDatosSelects()
    {
        // Fichas disponibles
        $this->fichas = FichaCaracterizacion::with(['programaFormacion'])
            ->where('status', true)
            ->orderBy('ficha')
            ->get();

        // Si es edición, cargar la persona específica
        if ($this->isEdit && $this->aprendiz && $this->aprendiz->persona) {
            $this->personas = collect([$this->aprendiz->persona])
                ->map(function ($persona) {
                    $persona->nombre_completo = trim($persona->primer_nombre . ' ' . 
                        ($persona->segundo_nombre ?? '') . ' ' . 
                        $persona->primer_apellido . ' ' . 
                        ($persona->segundo_apellido ?? ''));
                    return $persona;
                });
        } else {
            // Personas disponibles que no son aprendices
            $this->personas = Persona::whereDoesntHave('aprendiz')
                ->select('id', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'numero_documento')
                ->get()
                ->map(function ($persona) {
                    $persona->nombre_completo = trim($persona->primer_nombre . ' ' . 
                        ($persona->segundo_nombre ?? '') . ' ' . 
                        $persona->primer_apellido . ' ' . 
                        ($persona->segundo_apellido ?? ''));
                    return $persona;
                });
            
            // Si no hay personas disponibles, mostrar todas para prueba
            if ($this->personas->count() === 0) {
                $this->personas = Persona::select('id', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'numero_documento')
                    ->limit(10) // Limitar a 10 para prueba
                    ->get()
                    ->map(function ($persona) {
                        $persona->nombre_completo = trim($persona->primer_nombre . ' ' . 
                            ($persona->segundo_nombre ?? '') . ' ' . 
                            $persona->primer_apellido . ' . ' . // Added a dot here
                            ($persona->segundo_apellido ?? ''));
                        return $persona;
                    });
            }
        }
    }

    private function loadAprendizData()
    {
        if (!$this->aprendiz) return;

        // Datos del aprendiz (simplificado)
        $this->persona_id = $this->aprendiz->persona_id;
        $this->ficha_caracterizacion_id = $this->aprendiz->ficha_caracterizacion_id;
        $this->estado = $this->aprendiz->estado;
    }

    protected function rules()
    {
        $rules = [
            // Datos del aprendiz (simplificado como create.blade.php)
            'persona_id' => 'required|exists:personas,id',
            'ficha_caracterizacion_id' => 'required|exists:fichas_caracterizacion,id',
            'estado' => 'required|boolean',
        ];

        // Si es edición, permitir que la persona ya esté asignada
        if ($this->isEdit) {
            unset($rules['persona_id']);
        }

        return $rules;
    }

    protected function messages()
    {
        return [
            'persona_id.required' => 'La persona es obligatoria.',
            'persona_id.exists' => 'La persona seleccionada no es válida.',
            'ficha_caracterizacion_id.required' => 'La ficha es obligatoria.',
            'ficha_caracterizacion_id.exists' => 'La ficha seleccionada no es válida.',
            'estado.required' => 'El estado es obligatorio.',
        ];
    }

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();
            
            if ($this->isEdit) {
                // Editar aprendiz existente
                $this->aprendiz->update([
                    'ficha_caracterizacion_id' => $this->ficha_caracterizacion_id,
                    'estado' => $this->estado,
                ]);
                
                $message = 'Aprendiz actualizado correctamente';
                $this->dispatch('aprendizActualizado');
            } else {
                // Crear nuevo aprendiz (asignar persona existente)
                Aprendiz::create([
                    'persona_id' => $this->persona_id,
                    'ficha_caracterizacion_id' => $this->ficha_caracterizacion_id,
                    'estado' => $this->estado,
                ]);
                
                $message = 'Aprendiz creado correctamente';
                $this->dispatch('aprendizCreado');
            }
            
            DB::commit();
            
            // Notificar éxito
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $message,
            ]);
            
            // Cerrar modal
            $this->dispatch('closeModal');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al guardar el aprendiz: ' . $e->getMessage(),
            ]);
        }
    }

    private function prepareDataForService()
    {
        return [
            'ficha_caracterizacion_id' => $this->ficha_caracterizacion_id,
            'estado' => $this->estado,
            'persona' => [
                'tipo_documento_id' => $this->tipo_documento_id,
                'numero_documento' => $this->numero_documento,
                'primer_nombre' => $this->primer_nombre,
                'segundo_nombre' => $this->segundo_nombre,
                'primer_apellido' => $this->primer_apellido,
                'segundo_apellido' => $this->segundo_apellido,
                'email' => $this->email,
                'telefono' => $this->telefono,
                'direccion' => $this->direccion,
                'barrio' => $this->barrio,
                'municipio_id' => $this->municipio_id,
            ],
        ];
    }

    public function updatedDepartamentoId()
    {
        // Filtrar municipios por departamento seleccionado
        if ($this->departamento_id) {
            $this->municipios = collect([
                ['id' => 1, 'departamento_id' => 1, 'nombre' => 'Medellín'],
                ['id' => 2, 'departamento_id' => 1, 'nombre' => 'Envigado'],
                ['id' => 3, 'departamento_id' => 2, 'nombre' => 'Cali'],
                ['id' => 4, 'departamento_id' => 3, 'nombre' => 'Bogotá'],
            ])->where('departamento_id', $this->departamento_id);
        } else {
            // Resetear municipios si no hay departamento seleccionado
            $this->municipios = collect([]);
        }
        
        // Resetear municipio seleccionado
        $this->municipio_id = null;
    }

    public function cancel()
    {
        $this->dispatch('closeModal');
    }

    public function render()
    {
        return view('livewire.aprendices.aprendiz-form');
    }
}
