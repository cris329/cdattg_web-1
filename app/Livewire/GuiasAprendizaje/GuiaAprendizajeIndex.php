<?php

namespace App\Livewire\GuiasAprendizaje;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\GuiasAprendizaje;
use App\Models\ResultadosAprendizaje;
use Livewire\Attributes\On;

class GuiaAprendizajeIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $statusFilter = '';
    public $resultadoFilter = '';
    
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showShowModal = false;
    public $showDeleteModal = false;
    public $showGestionarResultadosModal = false;
    public $selectedGuia = null;
    public $selectedId = null;
    
    // Propiedades para gestión de resultados
    public $searchResultados = '';
    public $resultadosSeleccionados = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'statusFilter' => ['except' => ''],
        'resultadoFilter' => ['except' => ''],
    ];

    protected $listeners = [
        'guiaCreada' => '$refresh',
        'guiaActualizada' => '$refresh',
        'guiaEliminada' => '$refresh',
        'closeModal' => 'handleCloseModal',
        'notify' => 'showNotification',
        'refreshModal' => 'handleRefreshModal',
        'refreshPagination' => '$refresh',
        'confirmAction' => 'handleConfirmedAction',
    ];

    public function mount()
    {
        \Log::info('GuiaAprendizajeIndex mounted');
    }

    public function render()
    {
        $query = GuiasAprendizaje::with(['resultadosAprendizaje', 'actividades', 'userCreate', 'userEdit']);
        
        \Log::info('GuiaAprendizajeIndex render - Filtros:', [
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
            'resultadoFilter' => $this->resultadoFilter,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'perPage' => $this->perPage,
            'page' => $this->page ?? 1
        ]);
        
        // Filtro de búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('codigo', 'like', '%' . $this->search . '%')
                  ->orWhere('nombre', 'like', '%' . $this->search . '%');
            });
        }
        
        // Filtro de estado
        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter === '1');
        }
        
        // Filtro por resultado de aprendizaje
        if ($this->resultadoFilter !== '') {
            $query->whereHas('resultadosAprendizaje', function ($q) {
                $q->where('resultados_aprendizaje.id', $this->resultadoFilter);
            });
        }
        
        // Ordenamiento
        $query->orderBy($this->sortField, $this->sortDirection);
        
        $guias = $query->paginate($this->perPage);
        
        \Log::info('GuiaAprendizajeIndex render - Guías:', [
            'total' => $guias->total(),
            'count' => $guias->count(),
            'currentPage' => $guias->currentPage(),
            'lastPage' => $guias->lastPage(),
            'perPage' => $guias->perPage(),
            'hasMorePages' => $guias->hasMorePages()
        ]);

        // Obtener resultados de aprendizaje para filtros
        $resultados = ResultadosAprendizaje::orderBy('nombre')->get();

        return view('livewire.guias-aprendizaje.guia-aprendizaje-index', compact('guias', 'resultados'));
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function openEditModal($guiaId)
    {
        $this->selectedGuia = GuiasAprendizaje::with(['resultadosAprendizaje'])->find($guiaId);
        $this->showEditModal = true;
        
        if ($this->showShowModal) {
            $this->showShowModal = false;
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->selectedGuia = null;
    }
    
    public function openDeleteModal($guiaId)
    {
        $this->selectedGuia = GuiasAprendizaje::find($guiaId);
        $this->showDeleteModal = true;
        
        if ($this->showShowModal) {
            $this->showShowModal = false;
        }
        if ($this->showEditModal) {
            $this->showEditModal = false;
        }
    }
    
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedGuia = null;
    }

    public function openShowModal($guiaId)
    {
        $this->selectedGuia = GuiasAprendizaje::with(['resultadosAprendizaje', 'actividades', 'userCreate', 'userEdit'])->find($guiaId);
        $this->showShowModal = true;
    }

    public function handleCloseModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showShowModal = false;
        $this->showDeleteModal = false;
        $this->selectedGuia = null;
    }

    public function showNotification($data)
    {
        // Este método maneja las notificaciones desde el backend
        // El JavaScript se encargará de mostrarlas visualmente
    }

    public function handleRefreshModal()
    {
        // Este método se llama cuando se necesita refrescar el modal
        // Forzamos la recarga de los datos del selectedGuia
        if ($this->selectedGuia) {
            $this->selectedGuia->refresh();
        }
    }

    public function closeShowModal()
    {
        $this->showShowModal = false;
        $this->selectedGuia = null;
    }

    
    public function deleteGuia($guiaId)
    {
        $guia = GuiasAprendizaje::with(['actividades', 'resultadosAprendizaje'])->find($guiaId);
        
        if (!$guia) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Guía de aprendizaje no encontrada',
            ]);
            return;
        }
        
        // Verificar si tiene actividades asociadas
        if ($guia->actividades->count() > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se puede eliminar la guía. Tiene ' . $guia->actividades->count() . ' actividad(es) asociada(s).',
            ]);
            return;
        }
        
        try {
            $codigo = $guia->codigo;
            
            // Desasociar resultados antes de eliminar
            $guia->resultadosAprendizaje()->detach();
            
            $guia->delete();
            $this->closeDeleteModal();
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => "Guía de aprendizaje '{$codigo}' eliminada correctamente",
            ]);
            $this->dispatch('guiaEliminada');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar la guía: ' . $e->getMessage(),
            ]);
        }
    }

    public function toggleStatus($guiaId)
    {
        $guia = GuiasAprendizaje::find($guiaId);
        
        if ($guia) {
            // Cambiar estado usando booleano
            $guia->status = !$guia->status;
            $guia->user_edit_id = auth()->id();
            $guia->save();

            $statusText = $guia->status ? 'activada' : 'desactivada';
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Guía de aprendizaje {$statusText} correctamente"
            ]);

            // Si estamos en el modal de detalles, actualizar los datos pero mantener el modal abierto
            if ($this->showShowModal && $this->selectedGuia && $this->selectedGuia->id == $guiaId) {
                // Recargar los datos actualizados del modelo
                $this->selectedGuia->refresh();
                
                // Forzar re-render del componente para actualizar la UI
                $this->dispatch('refreshModal');
            }
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatedSearch()
    {
        $this->resetPage();
        \Log::info('Search updated: ' . $this->search);
    }
    
    public function updatedStatusFilter()
    {
        $this->resetPage();
        \Log::info('Status filter updated: ' . $this->statusFilter);
    }
    
    public function updatedResultadoFilter()
    {
        $this->resetPage();
        \Log::info('Resultado filter updated: ' . $this->resultadoFilter);
    }
    
    public function updatedPerPage()
    {
        $this->resetPage();
        \Log::info('PerPage updated: ' . $this->perPage);
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resultadoFilter = '';
        $this->resetPage();
        \Log::info('Filters cleared');
    }
    
    // Método específico para paginación (no resetear página)
    public function gotoPage($page)
    {
        \Log::info('gotoPage called with page: ' . $page);
        $this->page = $page;
    }

    public function openGestionarResultados($guiaId)
    {
        $this->selectedGuia = GuiasAprendizaje::with(['resultadosAprendizaje'])->find($guiaId);
        
        if (!$this->selectedGuia) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Guía de aprendizaje no encontrada',
            ]);
            return;
        }
        
        $this->showGestionarResultadosModal = true;
        
        if ($this->showShowModal) {
            $this->showShowModal = false;
        }
    }
    
    public function closeGestionarResultadosModal()
    {
        $this->showGestionarResultadosModal = false;
        $this->selectedGuia = null;
        $this->searchResultados = '';
        $this->resultadosSeleccionados = [];
        $this->selectAll = false;
    }
    
    // Métodos para gestión de resultados en la modal
    public function getResultadosDisponibles()
    {
        $query = ResultadosAprendizaje::query();
        
        // Aplicar búsqueda
        if ($this->searchResultados) {
            $query->where(function($q) {
                $q->where('codigo', 'like', '%' . $this->searchResultados . '%')
                  ->orWhere('nombre', 'like', '%' . $this->searchResultados . '%');
            });
        }
        
        // Excluir resultados ya asignados a la guía actual
        if ($this->selectedGuia) {
            $resultadosAsignados = $this->selectedGuia->resultadosAprendizaje->pluck('id')->toArray();
            $query->whereNotIn('id', $resultadosAsignados);
        }
        
        return $query->orderBy('codigo')->get();
    }
    
    public function refreshResultados()
    {
        $this->searchResultados = '';
        $this->resultadosSeleccionados = [];
        $this->selectAll = false;
    }
    
    public function asignarResultado($resultadoId)
    {
        if (!$this->selectedGuia) return;
        
        $resultado = ResultadosAprendizaje::find($resultadoId);
        if (!$resultado) return;
        
        // Verificar si ya está asignado
        if (!$this->selectedGuia->resultadosAprendizaje()->where('resultados_aprendizajes.id', $resultadoId)->exists()) {
            $this->selectedGuia->resultadosAprendizaje()->attach($resultadoId, [
                'user_create_id' => auth()->id(),
                'user_edit_id' => auth()->id(),
            ]);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Resultado de aprendizaje asignado correctamente'
            ]);
        }
        
        $this->refreshResultados();
    }
    
    public function desasignarResultado($resultadoId)
    {
        if (!$this->selectedGuia) return;
        
        $this->selectedGuia->resultadosAprendizaje()->detach($resultadoId);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Resultado de aprendizaje desasignado correctamente'
        ]);
        
        $this->refreshResultados();
    }
    
    public function asignarSeleccionados()
    {
        if (!$this->selectedGuia || empty($this->resultadosSeleccionados)) return;
        
        foreach ($this->resultadosSeleccionados as $resultadoId) {
            // Verificar si ya está asignado
            if (!$this->selectedGuia->resultadosAprendizaje()->where('resultados_aprendizajes.id', $resultadoId)->exists()) {
                $this->selectedGuia->resultadosAprendizaje()->attach($resultadoId, [
                    'user_create_id' => auth()->id(),
                    'user_edit_id' => auth()->id(),
                ]);
            }
        }
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => count($this->resultadosSeleccionados) . ' resultados asignados correctamente'
        ]);
        
        $this->refreshResultados();
    }
    
    public function desasignarTodos()
    {
        if (!$this->selectedGuia) return;
        
        $resultadosCount = $this->selectedGuia->resultadosAprendizaje->count();
        $this->selectedGuia->resultadosAprendizaje()->detach();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $resultadosCount . ' resultados desasignados correctamente'
        ]);
        
        $this->refreshResultados();
    }
    
    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->resultadosSeleccionados = $this->getResultadosDisponibles()->pluck('id')->toArray();
        } else {
            $this->resultadosSeleccionados = [];
        }
    }

    public function handleConfirmedAction($action, $params)
    {
        try {
            switch ($action) {
                case 'eliminarGuia':
                    $this->deleteGuia($params);
                    break;
            }
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Acción confirmada exitosamente'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al ejecutar la acción: ' . $e->getMessage()
            ]);
        }
    }
}
