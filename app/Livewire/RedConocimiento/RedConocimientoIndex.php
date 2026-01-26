<?php

namespace App\Livewire\RedConocimiento;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RedConocimiento;
use App\Models\Regional;
use Livewire\Attributes\On;

class RedConocimientoIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;  // Valor por defecto más razonable
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $statusFilter = '';
    public $regionalFilter = '';
    
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showShowModal = false;
    public $showDeleteModal = false;
    public $selectedRed = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],  // Cambiar el except también
        'statusFilter' => ['except' => ''],
        'regionalFilter' => ['except' => ''],
    ];

    protected $listeners = [
        'redCreada' => '$refresh',
        'redActualizada' => '$refresh',
        'redEliminada' => '$refresh',
        'closeModal' => 'handleCloseModal',
        'notify' => 'showNotification',
        'refreshModal' => 'handleRefreshModal',
    ];

    public function mount()
    {
        // No establecer perPage aquí, ya que se establece en el queryString
        // $this->perPage = 15;  // ← Esto causa el problema
        \Log::info('RedConocimientoIndex mounted');
    }

    public function render()
    {
        $query = RedConocimiento::with(['regional']);
        
        // Debug logging
        \Log::info('RedConocimientoIndex render - Filtros:', [
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
            'regionalFilter' => $this->regionalFilter,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'perPage' => $this->perPage
        ]);
        
        // Filtro de búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhereHas('regional', function ($subQuery) {
                      $subQuery->where('nombre', 'like', '%' . $this->search . '%');
                  });
            });
        }
        
        // Filtro de estado
        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter === '1');
        }
        
        // Filtro de regional
        if ($this->regionalFilter !== '') {
            $query->where('regionals_id', $this->regionalFilter);
        }
        
        // Ordenamiento
        $query->orderBy($this->sortField, $this->sortDirection);
        
        $redes = $query->paginate($this->perPage);
        
        // Debug logging
        \Log::info('RedConocimientoIndex render - Resultados:', [
            'total' => $redes->total(),
            'count' => $redes->count()
        ]);

        return view('livewire.red-conocimiento.red-conocimiento-index', compact('redes'));
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

    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->selectedRed = null;
    }

    public function openEditModal($redId)
    {
        $this->selectedRed = RedConocimiento::find($redId);
        $this->showEditModal = true;
        
        if ($this->showShowModal) {
            $this->showShowModal = false;
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->selectedRed = null;
    }

    public function openShowModal($redId)
    {
        $this->selectedRed = RedConocimiento::with(['regional', 'programasFormacion'])->find($redId);
        $this->showShowModal = true;
    }

    public function handleCloseModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showShowModal = false;
        $this->showDeleteModal = false;
        $this->selectedRed = null;
    }

    public function showNotification($data)
    {
        // Este método maneja las notificaciones desde el backend
        // El JavaScript se encargará de mostrarlas visualmente
    }

    public function handleRefreshModal()
    {
        // Este método se llama cuando se necesita refrescar el modal
        // Forzamos la recarga de los datos del selectedRed
        if ($this->selectedRed) {
            $this->selectedRed->refresh();
        }
    }

    public function closeShowModal()
    {
        $this->showShowModal = false;
        $this->selectedRed = null;
    }

    public function confirmDelete($redId)
    {
        $this->selectedRed = RedConocimiento::with(['programasFormacion'])->find($redId);
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedRed = null;
    }

    public function deleteRed($redId)
    {
        $red = RedConocimiento::with(['programasFormacion'])->find($redId);
        
        if (!$red) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Red de conocimiento no encontrada',
            ]);
            return;
        }
        
        // Verificar si tiene programas asociados
        if ($red->programasFormacion->count() > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se puede eliminar la red. Tiene ' . $red->programasFormacion->count() . ' programas asociados.',
            ]);
            return;
        }
        
        try {
            $red->delete();
            $this->closeDeleteModal();
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Red de conocimiento eliminada correctamente',
            ]);
            $this->dispatch('redEliminada');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar la red: ' . $e->getMessage(),
            ]);
        }
    }

    public function toggleStatus($redId)
    {
        $red = RedConocimiento::find($redId);
        
        if ($red) {
            // Cambiar estado usando booleano
            $red->status = !$red->status;
            $red->user_edit_id = auth()->id();
            $red->save();

            $statusText = $red->status ? 'activada' : 'desactivada';
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Red de conocimiento {$statusText} correctamente"
            ]);

            // Si estamos en el modal de detalles, actualizar los datos pero mantener el modal abierto
            if ($this->showShowModal && $this->selectedRed && $this->selectedRed->id == $redId) {
                // Recargar los datos actualizados del modelo
                $this->selectedRed->refresh();
                
                // Forzar re-render del componente para actualizar la UI
                $this->dispatch('refreshModal');
            }
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatedStatusFilter()
    {
        $this->resetPage();
        \Log::info('Status filter updated: ' . $this->statusFilter);
    }
    
    public function updatedRegionalFilter()
    {
        $this->resetPage();
        \Log::info('Regional filter updated: ' . $this->regionalFilter);
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
        $this->regionalFilter = '';
        $this->resetPage();
        \Log::info('Filters cleared');
    }

    public function getRegionalesProperty()
    {
        return Regional::where('status', 1)->get();
    }
}
