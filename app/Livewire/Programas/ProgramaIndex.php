<?php

namespace App\Livewire\Programas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProgramaFormacion;
use App\Models\RedConocimiento;
use App\Models\Parametro;
use App\Models\NivelFormacion;
use App\Models\Tema;
use App\Services\ProgramaFormacionService;
use Livewire\Attributes\On;

class ProgramaIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;  // Valor por defecto más razonable
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // Filtros adicionales
    public $redConocimientoFilter = '';
    public $nivelFilter = '';
    public $statusFilter = '';
    
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showShowModal = false;
    public $showDeleteModal = false;
    public $selectedPrograma = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],  // Cambiar el except también
    ];

    protected $listeners = [
        'programaCreado' => '$refresh',
        'programaActualizado' => '$refresh',
        'programaEliminado' => '$refresh',
        'closeModal' => 'handleCloseModal',
        'notify' => 'showNotification',
    ];

    public function mount()
    {
        $this->perPage = 15;
    }

    public function render()
    {
        $query = ProgramaFormacion::with(['redConocimiento', 'nivelFormacion'])
            ->when($this->search, function ($query) {
                $query->where('codigo', 'like', '%' . $this->search . '%')
                      ->orWhere('nombre', 'like', '%' . $this->search . '%');
            })
            ->when($this->redConocimientoFilter, function ($query) {
                $query->where('red_conocimiento_id', $this->redConocimientoFilter);
            })
            ->when($this->nivelFilter, function ($query) {
                $query->where('nivel_formacion_id', $this->nivelFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $programas = $query->paginate($this->perPage);

        // Obtener datos para los filtros
        $redesConocimiento = RedConocimiento::orderBy('nombre')->get();
        
        // Obtener niveles de formación usando el mismo método que el formulario
        $nivelesFormacion = Parametro::whereIn('name', ['TÉCNICO', 'TECNÓLOGO', 'AUXILIAR', 'OPERARIO'])->orderBy('name')->get();

        return view('livewire.programas.programa-index', compact('programas', 'redesConocimiento', 'nivelesFormacion'));
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

    public function clearFilters()
    {
        $this->reset(['search', 'redConocimientoFilter', 'nivelFilter', 'statusFilter']);
    }

    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->selectedPrograma = null;
    }

    public function closeCreateEditModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->selectedPrograma = null;
    }

    public function openEditModal($programaId)
    {
        $this->selectedPrograma = ProgramaFormacion::find($programaId);
        $this->showEditModal = true;
        
        // Cerrar modal de detalles si está abierto
        if ($this->showShowModal) {
            $this->showShowModal = false;
        }
    }

    public function openShowModal($programaId)
    {
        $this->selectedPrograma = ProgramaFormacion::with(['redConocimiento', 'nivelFormacion', 'competencias'])->find($programaId);
        $this->showShowModal = true;
    }

    public function handleCloseModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showShowModal = false;
        $this->showDeleteModal = false;
        $this->selectedPrograma = null;
    }

    public function showNotification($data)
    {
        // Este método maneja las notificaciones desde el backend
        // El JavaScript se encargará de mostrarlas visualmente
    }

    public function closeShowModal()
    {
        $this->showShowModal = false;
        $this->selectedPrograma = null;
    }

    public function confirmDelete($programaId)
    {
        $this->selectedPrograma = ProgramaFormacion::with(['redConocimiento', 'competencias', 'fichasCaracterizacion'])->find($programaId);
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedPrograma = null;
    }

    public function deletePrograma($programaId)
    {
        $programa = ProgramaFormacion::with(['competencias', 'fichasCaracterizacion'])->find($programaId);
        
        if (!$programa) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Programa no encontrado',
            ]);
            return;
        }
        
        // Verificar si tiene competencias asociadas
        if ($programa->competencias->count() > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se puede eliminar el programa. Tiene ' . $programa->competencias->count() . ' competencias asociadas.',
            ]);
            return;
        }
        
        // Verificar si tiene fichas asociadas
        if ($programa->fichasCaracterizacion->count() > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se puede eliminar el programa. Tiene ' . $programa->fichasCaracterizacion->count() . ' fichas asociadas.',
            ]);
            return;
        }
        
        try {
            $programa->delete();
            $this->closeDeleteModal();
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Programa eliminado correctamente',
            ]);
            $this->dispatch('programaEliminado');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el programa: ' . $e->getMessage(),
            ]);
        }
    }

    public function toggleStatus($programaId)
    {
        $programa = ProgramaFormacion::find($programaId);
        
        if ($programa) {
            $programa->status = !$programa->status;
            $programa->user_edit_id = auth()->id();
            $programa->save();

            $statusText = $programa->status ? 'activado' : 'desactivado';
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Programa {$statusText} correctamente"
            ]);

            // Actualizar el programa seleccionado para reflejar el cambio en el modal
            if ($this->showShowModal && $this->selectedPrograma && $this->selectedPrograma->id == $programaId) {
                $this->selectedPrograma = $programa;
            }
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getRedesConocimientoProperty()
    {
        return RedConocimiento::all();
    }

    public function getNivelesFormacionProperty()
    {
        return Parametro::whereIn('name', ['TÉCNICO', 'TECNÓLOGO', 'AUXILIAR', 'OPERARIO'])->get();
    }
}
