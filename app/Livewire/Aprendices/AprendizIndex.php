<?php

namespace App\Livewire\Aprendices;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Aprendiz;
use App\Models\Persona;
use App\Models\FichaCaracterizacion;
use App\Models\ProgramaFormacion;
use App\Models\Regional;
use App\Services\AprendizService;
use Livewire\Attributes\On;

class AprendizIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $page = 1;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // Filtros adicionales
    public $fichaFilter = '';
    public $programaFilter = '';
    public $regionalFilter = '';
    public $statusFilter = '';
    
    // Modales
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showShowModal = false;
    public $showDeleteModal = false;
    public $selectedAprendiz = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'aprendizCreado' => '$refresh',
        'aprendizActualizado' => '$refresh',
        'aprendizEliminado' => '$refresh',
        'closeModal' => 'handleCloseModal',
        'notify' => 'showNotification',
    ];

    public function mount()
    {
        $this->perPage = 15;
    }

    public function render()
    {
        $query = Aprendiz::with([
            'persona',
            'fichaCaracterizacion.programaFormacion',
            'fichaCaracterizacion.sede.regional'
        ]);

        // Búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('persona', function($subQuery) {
                    $subQuery->where('primer_nombre', 'like', '%' . $this->search . '%')
                           ->orWhere('segundo_nombre', 'like', '%' . $this->search . '%')
                           ->orWhere('primer_apellido', 'like', '%' . $this->search . '%')
                           ->orWhere('segundo_apellido', 'like', '%' . $this->search . '%')
                           ->orWhere('numero_documento', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('fichaCaracterizacion', function($subQuery) {
                    $subQuery->where('ficha', 'like', '%' . $this->search . '%');
                });
            });
        }

        // Filtros
        if ($this->fichaFilter) {
            $query->where('ficha_caracterizacion_id', $this->fichaFilter);
        }

        if ($this->programaFilter) {
            $query->whereHas('fichaCaracterizacion', function($q) {
                $q->where('programa_formacion_id', $this->programaFilter);
            });
        }

        if ($this->regionalFilter) {
            $query->whereHas('fichaCaracterizacion.sede', function($q) {
                $q->where('regional_id', $this->regionalFilter);
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('estado', $this->statusFilter);
        }

        // Ordenamiento
        if ($this->sortField === 'nombre') {
            $query->orderBy(
                Persona::selectRaw("CONCAT(primer_nombre, ' ', primer_apellido)")
                     ->whereColumn('personas.id', 'aprendices.persona_id'),
                $this->sortDirection
            );
        } elseif ($this->sortField === 'ficha') {
            $query->orderBy(
                FichaCaracterizacion::select('ficha')
                     ->whereColumn('fichas_caracterizacion.id', 'aprendices.ficha_caracterizacion_id'),
                $this->sortDirection
            );
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $aprendices = $query->paginate($this->perPage);

        // Debug temporal para verificar la consulta
        \Log::info('Aprendices query debug', [
            'perPage' => $this->perPage,
            'total' => $aprendices->total(),
            'currentPage' => $aprendices->currentPage(),
            'count' => $aprendices->count(),
            'hasItems' => $aprendices->count() > 0
        ]);

        // Cargar datos para filtros
        $fichas = FichaCaracterizacion::where('status', true)
            ->with('programaFormacion')
            ->orderBy('ficha')
            ->get();

        $programas = ProgramaFormacion::where('status', true)
            ->orderBy('nombre')
            ->get();

        $regionales = Regional::where('status', true)
            ->orderBy('nombre')
            ->get();

        return view('livewire.aprendices.aprendiz-index', [
            'aprendices' => $aprendices,
            'fichas' => $fichas,
            'programas' => $programas,
            'regionales' => $regionales,
        ]);
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
        $this->search = '';
        $this->fichaFilter = '';
        $this->programaFilter = '';
        $this->regionalFilter = '';
        $this->statusFilter = '';
        $this->perPage = 15;
    }

    // Métodos para modales
    public function openCreateModal()
    {
        $this->showCreateModal = true;
        $this->showEditModal = false;
        $this->showShowModal = false;
        $this->showDeleteModal = false;
    }

    public function openEditModal($aprendizId)
    {
        $this->selectedAprendiz = Aprendiz::with([
            'persona',
            'fichaCaracterizacion.programaFormacion',
            'fichaCaracterizacion.sede.regional'
        ])->find($aprendizId);
        
        if (!$this->selectedAprendiz) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Aprendiz no encontrado',
            ]);
            return;
        }

        $this->showEditModal = true;
        $this->showCreateModal = false;
        $this->showShowModal = false;
        $this->showDeleteModal = false;
    }

    public function openShowModal($aprendizId)
    {
        $this->selectedAprendiz = Aprendiz::with([
            'persona',
            'fichaCaracterizacion.programaFormacion.redConocimiento.regional',
            'fichaCaracterizacion.sede',
            'fichaCaracterizacion.ambiente'
        ])->find($aprendizId);
        
        if (!$this->selectedAprendiz) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Aprendiz no encontrado',
            ]);
            return;
        }

        $this->showShowModal = true;
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
    }

    public function openDeleteModal($aprendizId)
    {
        $this->selectedAprendiz = Aprendiz::find($aprendizId);
        
        if (!$this->selectedAprendiz) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Aprendiz no encontrado',
            ]);
            return;
        }

        $this->showDeleteModal = true;
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showShowModal = false;
    }

    // Métodos para cerrar modales
    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->selectedAprendiz = null;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->selectedAprendiz = null;
    }

    public function closeShowModal()
    {
        $this->showShowModal = false;
        $this->selectedAprendiz = null;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedAprendiz = null;
    }

    public function handleCloseModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showShowModal = false;
        $this->showDeleteModal = false;
        $this->selectedAprendiz = null;
    }

    // Acciones
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function deleteAprendiz($aprendizId)
    {
        $aprendiz = Aprendiz::find($aprendizId);
        
        if (!$aprendiz) {
            return;
        }

        try {
            $aprendiz->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Aprendiz eliminado correctamente',
            ]);
            
            $this->dispatch('aprendizEliminado');
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el aprendiz: ' . $e->getMessage(),
            ]);
        }
    }

    public function toggleStatus($aprendizId)
    {
        $aprendiz = Aprendiz::find($aprendizId);
        
        if (!$aprendiz) {
            return;
        }

        try {
            $previousStatus = $aprendiz->estado;
            $aprendiz->estado = !$aprendiz->estado;
            $aprendiz->save();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $aprendiz->estado ? 'Aprendiz activado correctamente' : 'Aprendiz desactivado correctamente',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al cambiar estado: ' . $e->getMessage(),
            ]);
        }
    }

    public function showNotification($data)
    {
        // Este método manejará las notificaciones desde el componente
        // La lógica de visualización está en el JavaScript
    }

    // Método para obtener estado formateado
    public function obtenerEstadoFormateado($aprendiz)
    {
        return [
            'status' => $aprendiz->estado,
            'texto' => $aprendiz->estado ? 'Activo' : 'Inactivo',
            'clase' => $aprendiz->estado ? 'success' : 'danger',
        ];
    }
}
