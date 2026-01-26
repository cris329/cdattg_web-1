<?php

namespace App\Livewire\Instructores;

use App\Models\Instructor;
use App\Models\Persona;
use App\Models\Regional;
use App\Models\RedConocimiento;
use App\Services\InstructorService;
use App\Services\InstructorBusinessRulesService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class InstructorIndex extends Component
{
    use WithPagination;

    // Búsqueda y filtros
    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $statusFilter = '';
    public $especialidadFilter = '';
    public $regionalFilter = '';

    // Modales
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showShowModal = false;
    public $showDeleteModal = false;
    public $showEspecialidadesModal = false;
    public $showFichasModal = false;

    // Datos seleccionados
    public $selectedInstructor = null;
    public $fichasAsignadas = null;
    public $especialidadesAsignadas = null;
    public $redesConocimientoDisponibles = null;
    public $selectedId = null;

    // Datos para filtros
    public $regionales = [];
    public $especialidades = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'statusFilter' => ['except' => ''],
        'especialidadFilter' => ['except' => ''],
        'regionalFilter' => ['except' => ''],
    ];

    protected $listeners = [
        'instructorCreado' => '$refresh',
        'instructorActualizado' => '$refresh',
        'instructorEliminado' => '$refresh',
        'closeModal' => 'handleCloseModal',
        'notify' => 'showNotification',
        'refreshPagination' => '$refresh',
    ];

    protected $instructorService;
    protected $businessRulesService;

    public function boot(InstructorService $instructorService, InstructorBusinessRulesService $businessRulesService)
    {
        $this->instructorService = $instructorService;
        $this->businessRulesService = $businessRulesService;
    }

    public function mount()
    {
        $this->cargarDatosFiltros();
    }

    private function cargarDatosFiltros()
    {
        $this->regionales = Regional::where('status', true)
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->toArray();

        $this->especialidades = RedConocimiento::where('status', true)
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->toArray();
    }

    public function render()
    {
        $filtros = [
            'search' => $this->search,
            'estado' => $this->statusFilter ?: 'todos',
            'especialidad' => $this->especialidadFilter,
            'regional' => $this->regionalFilter,
            'per_page' => $this->perPage,
        ];

        // Usar el servicio existente para obtener los instructores
        $instructores = $this->instructorService->listarConFiltros($filtros);

        return view('livewire.instructores.instructor-index', [
            'instructores' => $instructores,
        ]);
    }

    // Métodos para modales
    public function openCreateModal()
    {
        $this->showCreateModal = true;
        $this->showEditModal = false;
        $this->showShowModal = false;
        $this->showDeleteModal = false;
    }

    public function openEditModal($instructorId)
    {
        $this->selectedInstructor = Instructor::with([
            'persona', 
            'regional', 
            'centroFormacion',
            'tipoVinculacion',
            'nivelAcademico'
        ])->find($instructorId);
        
        if (!$this->selectedInstructor) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Instructor no encontrado',
            ]);
            return;
        }

        $this->showEditModal = true;
        $this->showCreateModal = false;
        $this->showShowModal = false;
        $this->showDeleteModal = false;
    }

    public function openShowModal($instructorId)
    {
        $this->selectedInstructor = Instructor::with([
            'persona',
            'regional',
            'centroFormacion',
            'tipoVinculacion',
            'nivelAcademico',
            'userCreated',
            'userEdited'
        ])->find($instructorId);
        
        if (!$this->selectedInstructor) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Instructor no encontrado',
            ]);
            return;
        }

        $this->showShowModal = true;
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
    }

    public function openDeleteModal($instructorId)
    {
        $this->selectedInstructor = Instructor::find($instructorId);
        
        if (!$this->selectedInstructor) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Instructor no encontrado',
            ]);
            return;
        }

        $this->showDeleteModal = true;
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showShowModal = false;
    }

    public function openEspecialidadesModal($instructorId)
    {
        $this->selectedInstructor = Instructor::with([
            'persona',
            'regional'
        ])->find($instructorId);
        
        if (!$this->selectedInstructor) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Instructor no encontrado',
            ]);
            return;
        }

        // Cargar especialidades asignadas
        $especialidades = $this->selectedInstructor->especialidades ?? [];
        $this->especialidadesAsignadas = [
            'principal' => $especialidades['principal'] ?? null,
            'secundarias' => $especialidades['secundarias'] ?? []
        ];

        // Cargar redes de conocimiento disponibles
        $this->redesConocimientoDisponibles = RedConocimiento::where('status', true)
            ->orderBy('nombre')
            ->get();

        $this->showEspecialidadesModal = true;
    }

    public function openFichasModal($instructorId)
    {
        $this->selectedInstructor = Instructor::with([
            'persona',
            'regional',
            'instructorFichas.ficha.programaFormacion'
        ])->find($instructorId);
        
        if (!$this->selectedInstructor) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Instructor no encontrado',
            ]);
            return;
        }

        // Cargar fichas asignadas
        $this->fichasAsignadas = $this->selectedInstructor->instructorFichas()
            ->with('ficha.programaFormacion')
            ->get();

        $this->showFichasModal = true;
    }

    // Métodos para cerrar modales
    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->selectedInstructor = null;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->selectedInstructor = null;
    }

    public function closeShowModal()
    {
        $this->showShowModal = false;
        $this->selectedInstructor = null;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedInstructor = null;
    }

    public function closeEspecialidadesModal()
    {
        $this->showEspecialidadesModal = false;
        $this->selectedInstructor = null;
    }

    public function closeFichasModal()
    {
        $this->showFichasModal = false;
        $this->selectedInstructor = null;
    }

    public function handleCloseModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showShowModal = false;
        $this->showDeleteModal = false;
        $this->showEspecialidadesModal = false;
        $this->showFichasModal = false;
        $this->selectedInstructor = null;
    }

    // Métodos de acciones
    public function deleteInstructor($instructorId)
    {
        try {
            $instructor = Instructor::find($instructorId);
            
            if (!$instructor) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Instructor no encontrado',
                ]);
                return;
            }

            // Verificar si tiene fichas asignadas antes de eliminar
            $fichasAsignadas = $this->businessRulesService->contarTotalFichasAsignadas($instructor);
            
            if ($fichasAsignadas > 0) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No se puede eliminar el instructor porque tiene ' . $fichasAsignadas . ' fichas asignadas',
                ]);
                return;
            }

            $codigo = $instructor->persona->numero_documento ?? $instructorId;
            
            // Eliminar usando el servicio
            $this->instructorService->eliminar($instructorId);
            
            $this->closeDeleteModal();
            
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => "Instructor '{$codigo}' eliminado correctamente",
            ]);
            
            $this->dispatch('instructorEliminado');
            
        } catch (\Exception $e) {
            \Log::error('Error eliminando instructor: ' . $e->getMessage());
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar instructor: ' . $e->getMessage(),
            ]);
        }
    }

    public function toggleStatus($instructorId)
    {
        try {
            $instructor = Instructor::find($instructorId);
            
            if (!$instructor) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Instructor no encontrado',
                ]);
                return;
            }

            // Obtener el estado actual antes de cambiarlo
            $estadoActual = $instructor->status;
            
            // Cambiar estado usando el servicio
            $this->instructorService->cambiarEstado($instructorId, !$estadoActual);
            
            // Mensaje correcto basado en el estado ANTES de cambiarlo
            $nuevoEstado = !$estadoActual ? 'activado' : 'inactivado';
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Instructor {$nuevoEstado} correctamente",
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error cambiando estado instructor: ' . $e->getMessage());
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al cambiar estado: ' . $e->getMessage(),
            ]);
        }
    }

    // Métodos de utilidad
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingEspecialidadFilter()
    {
        $this->resetPage();
    }

    public function updatingRegionalFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->especialidadFilter = '';
        $this->regionalFilter = '';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        
        $this->resetPage();
    }

    public function showNotification($data)
    {
        // Este método maneja las notificaciones desde el backend
        // El JavaScript se encargará de mostrarlas visualmente
        // NO volver a disparar notify para evitar bucle infinito
    }

    // Métodos para obtener datos de especialidades (similar a como se hace en el blade actual)
    private function obtenerEspecialidadesFormateadas($instructor)
    {
        $especialidades = $instructor->especialidades ?? [];
        $especialidadPrincipalId = $especialidades['principal'] ?? null;
        $especialidadesSecundariasIds = $especialidades['secundarias'] ?? [];
        
        $especialidadPrincipalNombre = null;
        if ($especialidadPrincipalId) {
            $redConocimiento = RedConocimiento::find($especialidadPrincipalId);
            $especialidadPrincipalNombre = $redConocimiento ? $redConocimiento->nombre : null;
        }
        
        $especialidadesSecundariasNombres = [];
        if (!empty($especialidadesSecundariasIds)) {
            $redesConocimiento = RedConocimiento::whereIn('id', $especialidadesSecundariasIds)->get();
            $especialidadesSecundariasNombres = $redesConocimiento->pluck('nombre')->toArray();
        }

        return [
            'principal' => $especialidadPrincipalNombre,
            'secundarias' => $especialidadesSecundariasNombres,
        ];
    }

    // Obtener personas disponibles para crear instructor
    public function getPersonasDisponiblesProperty()
    {
        return Persona::query()
            ->whereDoesntHave('instructor')
            ->orderBy('primer_nombre')
            ->orderBy('primer_apellido')
            ->get();
    }
}
