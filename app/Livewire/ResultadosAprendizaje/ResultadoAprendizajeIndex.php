<?php

namespace App\Livewire\ResultadosAprendizaje;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ResultadosAprendizaje;
use App\Models\Competencia;
use Livewire\Attributes\On;

class ResultadoAprendizajeIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $statusFilter = '';
    public $competenciaFilter = '';
    
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showShowModal = false;
    public $showDeleteModal = false;
    public $showCompetenciasModal = false;
    public $selectedResultado = null;
    public $selectedId = null;
    
    // Propiedades para gestión de competencias
    public $searchCompetencias = '';
    public $competenciasSeleccionadas = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'statusFilter' => ['except' => ''],
        'competenciaFilter' => ['except' => ''],
    ];

    protected $listeners = [
        'resultadoCreado' => '$refresh',
        'resultadoActualizado' => '$refresh',
        'resultadoEliminado' => '$refresh',
        'closeModal' => 'handleCloseModal',
        'notify' => 'showNotification',
        'refreshModal' => 'handleRefreshModal',
        'refreshPagination' => '$refresh',
        'competenciasActualizadas' => '$refresh',
    ];

    public function mount()
    {
        \Log::info('ResultadoAprendizajeIndex mounted');
    }

    public function render()
    {
        $query = ResultadosAprendizaje::with(['competencias', 'guiasAprendizaje', 'userCreate', 'userEdit']);
        
        \Log::info('ResultadoAprendizajeIndex render - Filtros:', [
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
            'competenciaFilter' => $this->competenciaFilter,
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
        
        // Filtro por competencia
        if ($this->competenciaFilter !== '') {
            $query->whereHas('competencias', function ($q) {
                $q->where('competencias.id', $this->competenciaFilter);
            });
        }
        
        // Ordenamiento
        $query->orderBy($this->sortField, $this->sortDirection);
        
        $resultados = $query->paginate($this->perPage);
        
        \Log::info('ResultadoAprendizajeIndex render - Resultados:', [
            'total' => $resultados->total(),
            'count' => $resultados->count(),
            'currentPage' => $resultados->currentPage(),
            'lastPage' => $resultados->lastPage(),
            'perPage' => $resultados->perPage(),
            'hasMorePages' => $resultados->hasMorePages()
        ]);

        // Obtener competencias para filtros
        $competencias = Competencia::orderBy('nombre')->get();

        return view('livewire.resultados-aprendizaje.resultado-aprendizaje-index', compact('resultados', 'competencias'));
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

    public function openEditModal($resultadoId)
    {
        $this->selectedResultado = ResultadosAprendizaje::with(['competencias'])->find($resultadoId);
        $this->showEditModal = true;
        
        if ($this->showShowModal) {
            $this->showShowModal = false;
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->selectedResultado = null;
    }

    public function openShowModal($resultadoId)
    {
        $this->selectedResultado = ResultadosAprendizaje::with(['competencias', 'guiasAprendizaje', 'userCreate', 'userEdit'])->find($resultadoId);
        $this->showShowModal = true;
    }

    public function handleCloseModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showShowModal = false;
        $this->showDeleteModal = false;
        $this->showCompetenciasModal = false;
        $this->selectedResultado = null;
    }

    public function showNotification($data)
    {
        // Este método maneja las notificaciones desde el backend
        // El JavaScript se encargará de mostrarlas visualmente
    }

    public function handleRefreshModal()
    {
        // Este método se llama cuando se necesita refrescar el modal
        // Forzamos la recarga de los datos del selectedResultado
        if ($this->selectedResultado) {
            $this->selectedResultado->refresh();
        }
    }

    public function closeShowModal()
    {
        $this->showShowModal = false;
        $this->selectedResultado = null;
    }

    public function confirmDelete($resultadoId)
    {
        $this->selectedResultado = ResultadosAprendizaje::with(['guiasAprendizaje', 'competencias'])->find($resultadoId);
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedResultado = null;
    }

    public function openCompetenciasModal($resultadoId)
    {
        $this->selectedResultado = ResultadosAprendizaje::with(['competencias'])->find($resultadoId);
        $this->showCompetenciasModal = true;
        
        if ($this->showShowModal) {
            $this->showShowModal = false;
        }
    }

    public function closeCompetenciasModal()
    {
        $this->showCompetenciasModal = false;
        $this->selectedResultado = null;
    }

    public function deleteResultado($resultadoId)
    {
        $resultado = ResultadosAprendizaje::with(['guiasAprendizaje', 'competencias'])->find($resultadoId);
        
        if (!$resultado) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Resultado de aprendizaje no encontrado',
            ]);
            return;
        }
        
        // Verificar si tiene guías asociadas
        if ($resultado->guiasAprendizaje->count() > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se puede eliminar el resultado. Tiene ' . $resultado->guiasAprendizaje->count() . ' guía(s) asociada(s).',
            ]);
            return;
        }
        
        try {
            $codigo = $resultado->codigo;
            
            // Desasociar competencias antes de eliminar
            $resultado->competencias()->detach();
            
            $resultado->delete();
            $this->closeDeleteModal();
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => "Resultado de aprendizaje '{$codigo}' eliminado correctamente",
            ]);
            $this->dispatch('resultadoEliminado');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el resultado: ' . $e->getMessage(),
            ]);
        }
    }

    public function toggleStatus($resultadoId)
    {
        $resultado = ResultadosAprendizaje::find($resultadoId);
        
        if ($resultado) {
            // Cambiar estado usando booleano
            $resultado->status = !$resultado->status;
            $resultado->user_edit_id = auth()->id();
            $resultado->save();

            $statusText = $resultado->status ? 'activado' : 'desactivado';
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Resultado de aprendizaje {$statusText} correctamente"
            ]);

            // Si estamos en el modal de detalles, actualizar los datos pero mantener el modal abierto
            if ($this->showShowModal && $this->selectedResultado && $this->selectedResultado->id == $resultadoId) {
                // Recargar los datos actualizados del modelo
                $this->selectedResultado->refresh();
                
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
    
    public function updatedCompetenciaFilter()
    {
        $this->resetPage();
        \Log::info('Competencia filter updated: ' . $this->competenciaFilter);
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
        $this->competenciaFilter = '';
        $this->resetPage();
        \Log::info('Filters cleared');
    }
    
    // Método específico para paginación (no resetear página)
    public function gotoPage($page)
    {
        \Log::info('gotoPage called with page: ' . $page);
        $this->page = $page;
    }
    
    // Método para formatear horas (similar al de competencias)
    public function formatearHoras($horas)
    {
        if ($horas == 0) {
            return '0';
        }
        
        return number_format($horas, 0, ',', '.');
    }
    
    // Métodos para gestión de competencias en la modal
    public function getCompetenciasDisponibles()
    {
        $query = Competencia::query();
        
        // Aplicar búsqueda
        if ($this->searchCompetencias) {
            $query->where(function($q) {
                $q->where('codigo', 'like', '%' . $this->searchCompetencias . '%')
                  ->orWhere('nombre', 'like', '%' . $this->searchCompetencias . '%');
            });
        }
        
        // Excluir competencias ya asignadas al resultado actual
        if ($this->selectedResultado) {
            $competenciasAsignadas = $this->selectedResultado->competencias->pluck('id')->toArray();
            $query->whereNotIn('id', $competenciasAsignadas);
        }
        
        return $query->orderBy('codigo')->get();
    }
    
    public function refreshCompetencias()
    {
        $this->searchCompetencias = '';
        $this->competenciasSeleccionadas = [];
        $this->selectAll = false;
    }
    
    public function asignarCompetencia($competenciaId)
    {
        if (!$this->selectedResultado) return;
        
        $competencia = Competencia::find($competenciaId);
        if (!$competencia) return;
        
        // Verificar si ya está asignada
        if (!$this->selectedResultado->competencias()->where('competencias.id', $competenciaId)->exists()) {
            $this->selectedResultado->competencias()->attach($competenciaId, [
                'user_create_id' => auth()->id(),
                'user_edit_id' => auth()->id(),
            ]);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Competencia asignada correctamente'
            ]);
        }
        
        $this->refreshCompetencias();
    }
    
    public function desasignarCompetencia($competenciaId)
    {
        if (!$this->selectedResultado) return;
        
        $this->selectedResultado->competencias()->detach($competenciaId);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Competencia desasignada correctamente'
        ]);
        
        $this->refreshCompetencias();
    }
    
    public function asignarSeleccionadas()
    {
        if (!$this->selectedResultado || empty($this->competenciasSeleccionadas)) return;
        
        foreach ($this->competenciasSeleccionadas as $competenciaId) {
            // Verificar si ya está asignada
            if (!$this->selectedResultado->competencias()->where('competencias.id', $competenciaId)->exists()) {
                $this->selectedResultado->competencias()->attach($competenciaId, [
                    'user_create_id' => auth()->id(),
                    'user_edit_id' => auth()->id(),
                ]);
            }
        }
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => count($this->competenciasSeleccionadas) . ' competencias asignadas correctamente'
        ]);
        
        $this->refreshCompetencias();
    }
    
    public function desasignarTodas()
    {
        if (!$this->selectedResultado) return;
        
        $competenciasCount = $this->selectedResultado->competencias->count();
        $this->selectedResultado->competencias()->detach();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $competenciasCount . ' competencias desasignadas correctamente'
        ]);
        
        $this->refreshCompetencias();
    }
    
    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->competenciasSeleccionadas = $this->getCompetenciasDisponibles()->pluck('id')->toArray();
        } else {
            $this->competenciasSeleccionadas = [];
        }
    }
}
