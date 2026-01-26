<?php

namespace App\Livewire\ResultadosAprendizaje;

use Livewire\Component;
use App\Models\ResultadosAprendizaje;
use App\Models\Competencia;

class ResultadoAprendizajeForm extends Component
{
    public $codigo;
    public $nombre;
    public $duracion;
    public $competencia_id;
    public $status = true;
    
    public $isEdit = false;
    public $resultadoId;

    protected $rules = [
        'codigo' => 'required|string|max:20|unique:resultados_aprendizajes,codigo',
        'nombre' => 'required|string|max:255',
        'duracion' => 'nullable|numeric|min:0|max:9999.99',
        'competencia_id' => 'nullable|exists:competencias,id',
        'status' => 'boolean',
    ];

    protected $messages = [
        'codigo.required' => 'El código es obligatorio',
        'codigo.unique' => 'Este código ya está registrado',
        'nombre.required' => 'El nombre es obligatorio',
        'duracion.numeric' => 'La duración debe ser un número',
        'duracion.min' => 'La duración no puede ser negativa',
        'duracion.max' => 'La duración máxima es 9999.99 horas',
        'competencia_id.exists' => 'La competencia seleccionada no es válida',
    ];

    public function mount($isEdit = false, $resultadoId = null)
    {
        $this->isEdit = $isEdit;
        $this->resultadoId = $resultadoId;
        $this->status = true;

        if ($isEdit && $resultadoId) {
            $this->loadResultado($resultadoId);
        }
    }

    public function loadResultado($resultadoId)
    {
        $resultado = ResultadosAprendizaje::with('competencias')->find($resultadoId);
        
        if (!$resultado) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Resultado de aprendizaje no encontrado'
            ]);
            return;
        }

        $this->codigo = $resultado->codigo;
        $this->nombre = $resultado->nombre;
        $this->duracion = $resultado->duracion;
        
        // Cargar la primera competencia si existe, si no dejar como null
        $primeraCompetencia = $resultado->competencias->first();
        $this->competencia_id = $primeraCompetencia ? $primeraCompetencia->id : null;
        
        $this->status = $resultado->status;
    }

    public function save()
    {
        if ($this->isEdit) {
            $this->rules['codigo'] = 'required|string|max:20|unique:resultados_aprendizajes,codigo,' . $this->resultadoId;
        }

        $this->validate();

        try {
            if ($this->isEdit) {
                // Actualizar
                $resultado = ResultadosAprendizaje::find($this->resultadoId);
                
                if (!$resultado) {
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => 'Resultado de aprendizaje no encontrado'
                    ]);
                    return;
                }

                $resultado->update([
                    'codigo' => strtoupper($this->codigo),
                    'nombre' => $this->nombre,
                    'duracion' => $this->duracion,
                    'status' => $this->status,
                    'user_edit_id' => auth()->id(),
                ]);

                // Manejar asociación con competencia
                if ($this->competencia_id) {
                    $competencia = Competencia::find($this->competencia_id);
                    if ($competencia) {
                        // Verificar si ya está asociado
                        if (!$resultado->competencias()->where('competencias.id', $this->competencia_id)->exists()) {
                            $resultado->competencias()->attach($this->competencia_id, [
                                'user_create_id' => auth()->id(),
                                'user_edit_id' => auth()->id(),
                            ]);
                            
                            // Redistribuir duración en la competencia
                            $this->redistribuirDuracionCompetencia($competencia);
                        }
                    }
                }

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Resultado de aprendizaje actualizado correctamente'
                ]);
                $this->dispatch('resultadoActualizado');
            } else {
                // Crear
                $resultado = ResultadosAprendizaje::create([
                    'codigo' => strtoupper($this->codigo),
                    'nombre' => $this->nombre,
                    'duracion' => $this->duracion,
                    'status' => $this->status,
                    'user_create_id' => auth()->id(),
                    'user_edit_id' => auth()->id(),
                ]);

                // Asociar a competencia si se seleccionó
                if ($this->competencia_id) {
                    $competencia = Competencia::find($this->competencia_id);
                    if ($competencia) {
                        $resultado->competencias()->attach($this->competencia_id, [
                            'user_create_id' => auth()->id(),
                            'user_edit_id' => auth()->id(),
                        ]);
                        
                        // Redistribuir duración en la competencia
                        $this->redistribuirDuracionCompetencia($competencia);
                    }
                }

                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Resultado de aprendizaje creado correctamente'
                ]);
                $this->dispatch('resultadoCreado');
            }

            $this->cancel();
        } catch (\Exception $e) {
            \Log::error('Error al guardar resultado de aprendizaje: ' . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al guardar el resultado: ' . $e->getMessage()
            ]);
        }
    }

    public function cancel()
    {
        $this->reset();
        $this->resetValidation();
        $this->mount();
        $this->dispatch('closeModal');
    }

    /**
     * Redistribuye la duración de la competencia entre todos sus resultados de aprendizaje
     */
    private function redistribuirDuracionCompetencia(Competencia $competencia): void
    {
        $resultados = $competencia->resultadosAprendizaje()->get();
        $totalResultados = $resultados->count();
        
        if ($totalResultados === 0) {
            return;
        }
        
        $duracionPorResultado = $competencia->duracion / $totalResultados;
        
        foreach ($resultados as $resultado) {
            // Actualizar duración en la tabla pivot
            \DB::table('resultados_aprendizaje_competencia')
                ->where('competencia_id', $competencia->id)
                ->where('rap_id', $resultado->id)
                ->update([
                    'duracion' => $duracionPorResultado,
                    'updated_at' => now(),
                ]);
            
            // Actualizar duración en la tabla resultados_aprendizajes
            $resultado->update([
                'duracion' => $duracionPorResultado,
            ]);
        }
    }

    public function render()
    {
        $competencias = Competencia::orderBy('nombre')->get();
        
        return view('livewire.resultados-aprendizaje.resultado-aprendizaje-form', compact('competencias'));
    }
}
