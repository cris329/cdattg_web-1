<?php

namespace App\Livewire\GuiasAprendizaje;

use Livewire\Component;
use App\Models\GuiasAprendizaje;
use App\Models\ResultadosAprendizaje;
use App\Models\GuiasResultados;

class GestionarResultados extends Component
{
    public $guia = null;
    public $resultadosAsignados;
    public $resultadosDisponibles;
    public $showAsignarModal = false;
    public $resultadoSeleccionado = null;
    public $showConfirmarCierre = false;
    
    protected $listeners = [
        'openGestionarResultadosModal' => 'abrirModal',
        'confirmAction' => 'handleConfirmedAction',
    ];

    public function boot()
    {
        $this->resultadosAsignados = collect();
        $this->resultadosDisponibles = collect();
    }

    public function abrirModal($data)
    {
        $guiaId = $data['guiaId'] ?? null;
        
        if (!$guiaId) {
            return;
        }
        
        $this->guia = GuiasAprendizaje::with(['resultadosAprendizaje' => function($query) {
            $query->withPivot('created_at', 'user_create_id');
        }])->find($guiaId);
        
        if (!$this->guia) {
            return;
        }
        
        $this->cargarResultados();
        $this->dispatch('showGestionarResultadosModal');
    }

    public function mount($guia = null)
    {
        if ($guia) {
            $this->guia = $guia;
            $this->cargarResultados();
        }
    }

    public function cargarResultados()
    {
        if (!$this->guia) {
            $this->resultadosAsignados = collect();
            $this->resultadosDisponibles = collect();
            return;
        }

        try {
            // Obtener resultados ya asignados a esta guía
            $this->resultadosAsignados = $this->guia->resultadosAprendizaje()
                ->withPivot('created_at', 'user_create_id')
                ->orderBy('codigo')
                ->get();

            // Obtener resultados disponibles (no asignados a esta guía)
            $asignadosIds = $this->resultadosAsignados->pluck('id')->toArray();
            
            $this->resultadosDisponibles = ResultadosAprendizaje::where('status', 1)
                ->whereNotIn('id', $asignadosIds)
                ->orderBy('codigo')
                ->get();
                
        } catch (\Exception $e) {
            \Log::error('Error en cargarResultados: ' . $e->getMessage());
            $this->resultadosAsignados = collect();
            $this->resultadosDisponibles = collect();
        }
    }

    public function asignarResultadoDirecto($resultadoId)
    {
        try {
            // Verificar que el resultado exista y no esté ya asignado
            $resultado = ResultadosAprendizaje::find($resultadoId);
            
            if (!$resultado) {
                return;
            }

            // Verificar si ya está asignado
            $yaAsignado = $this->guia->resultadosAprendizaje()
                ->where('resultados_aprendizajes.id', $resultadoId)
                ->exists();

            if ($yaAsignado) {
                return;
            }

            // Asignar el resultado
            $this->guia->resultadosAprendizaje()->attach($resultadoId, [
                'user_create_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Recargar los resultados
            $this->cargarResultados();

        } catch (\Exception $e) {
            // Solo loguear error, sin notificación
            \Log::error('Error en asignarResultadoDirecto: ' . $e->getMessage());
        }
    }

    public function desasignarResultado($resultadoId)
    {
        try {
            $resultado = ResultadosAprendizaje::find($resultadoId);
            
            if (!$resultado) {
                return;
            }

            // Desasociar el resultado
            $this->guia->resultadosAprendizaje()->detach($resultadoId);

            // Recargar los resultados
            $this->cargarResultados();

        } catch (\Exception $e) {
            // Solo loguear error, sin notificación
            \Log::error('Error en desasignarResultado: ' . $e->getMessage());
        }
    }

    public function openAsignarModal()
    {
        $this->showAsignarModal = true;
        $this->resultadoSeleccionado = null;
    }

    public function closeAsignarModal()
    {
        $this->showAsignarModal = false;
        $this->resultadoSeleccionado = null;
    }

    public function asignarResultado()
    {
        $this->validate([
            'resultadoSeleccionado' => 'required|exists:resultados_aprendizajes,id',
        ]);

        $this->asignarResultadoDirecto($this->resultadoSeleccionado);
        $this->closeAsignarModal();
    }

    public function exportarResultados()
    {
        try {
            // Lógica para exportar a PDF
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Función de exportación en desarrollo...',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al exportar: ' . $e->getMessage(),
            ]);
        }
    }

    public function verHistorial()
    {
        try {
            // Lógica para ver historial de cambios
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Función de historial en desarrollo...',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al cargar historial: ' . $e->getMessage(),
            ]);
        }
    }

    public function confirmarCierreGestion()
    {
        $this->showConfirmarCierre = true;
    }

    public function confirmarDesasignacion($resultadoId)
    {
        $resultado = ResultadosAprendizaje::find($resultadoId);
        
        if (!$resultado) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Resultado de aprendizaje no encontrado',
            ]);
            return;
        }

        // Usar la modal global del sistema
        $this->dispatch('showGlobalConfirmModal', [
            'title' => 'Confirmar desasignación',
            'message' => '¿Deseas desasignar este resultado de la guía?',
            'type' => 'danger',
            'action' => 'desasignarResultado',
            'params' => ['resultadoId' => $resultadoId],
            'codigo' => $resultado->codigo,
            'nombre' => $resultado->nombre,
            'itemType' => 'Resultado de Aprendizaje'
        ]);
    }

    public function desasociarResultado($resultadoId)
    {
        try {
            $resultado = ResultadosAprendizaje::find($resultadoId);
            
            if (!$resultado) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Resultado de aprendizaje no encontrado',
                ]);
                return;
            }

            // Desasociar el resultado
            $this->guia->resultadosAprendizaje()->detach($resultadoId);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Resultado '{$resultado->codigo}' desasignado correctamente",
            ]);

            // Recargar los resultados
            $this->cargarResultados();

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al desasignar resultado: ' . $e->getMessage(),
            ]);
        }
    }

    public function handleConfirmedAction($action, $params)
    {
        if ($action === 'desasignarResultado') {
            $this->desasignarResultado($params);
        } else if ($action === 'asignarResultado') {
            $this->asignarResultadoDirecto($params);
        }
    }

    public function closeModal()
    {
        $this->dispatch('closeGestionarResultadosModal');
    }

    public function render()
    {
        return view('livewire.guias-aprendizaje.gestionar-resultados');
    }
}