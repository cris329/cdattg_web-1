<?php

namespace App\Livewire\ResultadosAprendizaje;

use Livewire\Component;
use App\Models\ResultadosAprendizaje;
use App\Models\Competencia;
use Illuminate\Support\Facades\Log;
use Exception;

class GestionarCompetencias extends Component
{
    // Propiedades principales
    public $resultadoId;
    public $resultado;
    
    // Colecciones para la gestión
    public $asignados = [];
    public $disponibles = [];
    
    // Listeners para eventos
    protected $listeners = [
        'confirmAction' => 'handleConfirmedAction',
        'refreshComponent' => '$refresh',
    ];

    // Reglas de validación
    protected $rules = [
        // Reglas específicas si se necesitan
    ];

    public function mount($resultadoId)
    {
        $this->resultadoId = $resultadoId;
        $this->cargarDatos();
    }

    public function render()
    {
        return view('livewire.resultados-aprendizaje.gestionar-competencias');
    }

    /**
     * Cargar datos iniciales
     */
    public function cargarDatos()
    {
        try {
            $this->resultado = ResultadosAprendizaje::findOrFail($this->resultadoId);
            
            // Obtener asignados (usando relación many-to-many)
            $this->asignados = $this->resultado->competencias()
                ->orderBy('nombre')
                ->get();
            
            // Obtener disponibles (los que no están asignados)
            $asignadosIds = $this->asignados->pluck('id');
            $this->disponibles = Competencia::whereNotIn('id', $asignadosIds)
                ->orderBy('nombre')
                ->get();
                
        } catch (\Exception $e) {
            Log::error('Error cargando datos: ' . $e->getMessage());
            // Manejo de error silencioso
        }
    }

    /**
     * Manejar acciones confirmadas desde el modal
     */
    public function handleConfirmedAction($action, $params)
    {
        try {
            switch ($action) {
                case 'asignarCompetencia':
                    $this->asignarCompetencia($params);
                    break;
                case 'desasignarCompetencia':
                    $this->desasignarCompetencia($params);
                    break;
            }
            
            // Refrescar datos después de la acción
            $this->cargarDatos();
            
        } catch (\Exception $e) {
            Log::error('Error en acción confirmada: ' . $e->getMessage());
            // Notificación de error si es necesario
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al procesar la acción'
            ]);
        }
    }

    /**
     * Asignar un elemento
     */
    public function asignarCompetencia($elementoId)
    {
        try {
            $elemento = Competencia::findOrFail($elementoId);
            
            // Verificar si ya está asignado
            if ($this->resultado->competencias()->where('competencia_id', $elementoId)->exists()) {
                return; // Ya está asignado, no hacer nada
            }
            
            // Realizar la asignación (many-to-many)
            $this->resultado->competencias()->attach($elementoId, [
                'user_create_id' => auth()->id(),
                'user_edit_id' => auth()->id(),
            ]);
            
            // Notificación de éxito (opcional)
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "{$elemento->nombre} asignada correctamente"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error asignando competencia: ' . $e->getMessage());
            throw $e; // Re-lanzar para que el listener maneje
        }
    }

    /**
     * Desasignar un elemento
     */
    public function desasignarCompetencia($elementoId)
    {
        try {
            $elemento = Competencia::findOrFail($elementoId);
            
            // Realizar la desasignación (many-to-many)
            $this->resultado->competencias()->detach($elementoId);
            
            // Notificación de éxito (opcional)
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "{$elemento->nombre} desasignada correctamente"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error desasignando competencia: ' . $e->getMessage());
            throw $e; // Re-lanzar para que el listener maneje
        }
    }
}
