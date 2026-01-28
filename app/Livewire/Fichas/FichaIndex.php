<?php

namespace App\Livewire\Fichas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\FichaCaracterizacion;
use App\Models\ProgramaFormacion;
use App\Models\Regional;
use App\Models\Sede;
use App\Services\FichaService;
use Livewire\Attributes\On;

class FichaIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $page = 1;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // Filtros adicionales
    public $programaFilter = '';
    public $regionalFilter = '';
    public $sedeFilter = '';
    public $statusFilter = '';
    
    // Modales
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showShowModal = false;
    public $showDeleteModal = false;
    public $showGestionarAprendicesModal = false;
    public $selectedFicha = null;
    public $selectedPersonas = [];
    public $selectAllPersonas = false;
    public $searchPersona = '';
    public $personasDisponibles = [];
    
    // Propiedades para desasignación
    public $selectedAprendicesAsignados = [];
    public $selectAllAprendicesAsignados = false;
    
    // Datos para filtros
    public $programas;
    public $regionales;
    public $sedes;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'fichaCreada' => '$refresh',
        'fichaActualizada' => '$refresh',
        'fichaEliminada' => '$refresh',
        'closeModal' => 'handleCloseModal',
        'notify' => 'showNotification',
    ];

    public function mount()
    {
        $this->loadFiltersData();
    }

    private function loadFiltersData()
    {
        // Cargar datos para los filtros
        $this->programas = ProgramaFormacion::orderBy('nombre')->get();
        $this->regionales = Regional::orderBy('nombre')->get();
        $this->sedes = Sede::orderBy('sede')->get();
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
        $this->showEditModal = false;
        $this->selectedFicha = null;
    }

    public function openEditModal($fichaId)
    {
        $this->selectedFicha = FichaCaracterizacion::with([
            'programaFormacion', 
            'sede', 
            'instructor.persona',
            'ambiente'
        ])->find($fichaId);
        
        if ($this->selectedFicha) {
            $this->showEditModal = true;
            $this->showCreateModal = false;
        }
    }

    public function openShowModal($fichaId)
    {
        $this->selectedFicha = FichaCaracterizacion::with([
            'programaFormacion.redConocimiento.regional',
            'sede.regional',
            'instructor.persona',
            'ambiente',
            'aprendices.persona'
        ])->find($fichaId);
        
        if ($this->selectedFicha) {
            $this->showShowModal = true;
        }
    }

    public function openDeleteModal($fichaId)
    {
        $this->selectedFicha = FichaCaracterizacion::find($fichaId);
        
        if ($this->selectedFicha) {
            $this->showDeleteModal = true;
        }
    }

    public function closeCreateEditModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->selectedFicha = null;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedFicha = null;
    }

    public function handleCloseModal()
    {
        $this->closeCreateEditModals();
        $this->closeDeleteModal();
        $this->showShowModal = false;
    }

    public function toggleStatus($fichaId)
    {
        try {
            $ficha = FichaCaracterizacion::find($fichaId);
            if ($ficha) {
                $ficha->status = !$ficha->status;
                $ficha->save();
                
                $this->dispatch('notify', [
                    'type' => $ficha->status ? 'success' : 'warning', 
                    'message' => $ficha->status ? 'Ficha activada exitosamente' : 'Ficha desactivada'
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', 'error', 'Error al cambiar el estado de la ficha');
        }
    }

    public function deleteFicha($fichaId)
    {
        try {
            $ficha = FichaCaracterizacion::find($fichaId);
            if ($ficha) {
                // Verificar si tiene aprendices asociados
                if ($ficha->aprendices()->count() > 0) {
                    $this->dispatch('notify', ['type' => 'warning', 'message' => 'No se puede eliminar la ficha porque tiene aprendices asociados']);
                    return;
                }
                
                $ficha->delete();
                $this->dispatch('fichaEliminada');
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Ficha eliminada exitosamente']);
                $this->closeDeleteModal();
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error al eliminar la ficha']);
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->programaFilter = '';
        $this->regionalFilter = '';
        $this->sedeFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    // Métodos para gestión de aprendices
    public function openGestionarAprendices()
    {
        $this->showGestionarAprendicesModal = true;
        $this->loadPersonasDisponibles();
        $this->reset(['selectedPersonas', 'selectAllPersonas', 'searchPersona']);
    }

    public function openGestionarAprendicesDirect($fichaId)
    {
        // Log para depuración - qué ficha se está abriendo
        \Log::info('Abriendo gestión de aprendices - Ficha ID: ' . $fichaId);
        
        // Cargar la ficha con todas las relaciones necesarias incluyendo aprendices
        $this->selectedFicha = FichaCaracterizacion::with(['programaFormacion', 'sede', 'instructor.persona', 'ambiente', 'aprendices.persona'])->find($fichaId);
        
        // Log para depuración - qué se cargó
        \Log::info('Ficha cargada:', [
            'ficha_id' => $this->selectedFicha?->id,
            'ficha_codigo' => $this->selectedFicha?->ficha,
            'aprendices_count' => $this->selectedFicha?->aprendices->count(),
            'aprendices_ids' => $this->selectedFicha?->aprendices->pluck('id')->toArray(),
            'aprendices_data' => $this->selectedFicha?->aprendices->map(function($a) {
                return [
                    'id' => $a->id,
                    'persona_id' => $a->persona_id,
                    'persona_nombre' => $a->persona ? $a->persona->primer_nombre . ' ' . $a->persona->primer_apellido : 'N/A',
                    'estado' => $a->estado
                ];
            })->toArray()
        ]);
        
        $this->showGestionarAprendicesModal = true;
        $this->loadPersonasDisponibles();
        $this->reset(['selectedPersonas', 'selectAllPersonas', 'searchPersona']);
    }

    public function closeGestionarAprendicesModal()
    {
        $this->showGestionarAprendicesModal = false;
        $this->reset(['selectedPersonas', 'selectAllPersonas', 'searchPersona', 'personasDisponibles', 'selectedAprendicesAsignados', 'selectAllAprendicesAsignados']);
    }

    private function loadPersonasDisponibles()
    {
        if ($this->selectedFicha) {
            \Log::info('=== CARGANDO PERSONAS DISPONIBLES ===');
            \Log::info('Ficha seleccionada:', ['ficha_id' => $this->selectedFicha->id, 'ficha_codigo' => $this->selectedFicha->ficha]);
            
            // Obtener personas que no tienen rol de aprendiz en esta ficha específica
            // SIN LÍMITE para mostrar todas las disponibles
            $this->personasDisponibles = \App\Models\Persona::where('status', 1)
                ->whereNotIn('id', function ($query) {
                    $query->select('persona_id')
                        ->from('aprendices')
                        ->where('ficha_caracterizacion_id', $this->selectedFicha->id);
                })
                ->orderBy('primer_nombre')
                ->orderBy('primer_apellido')
                ->get(); // Eliminado el limit(50)
            
            \Log::info('Personas disponibles cargadas (SIN LÍMITE):', [
                'count' => $this->personasDisponibles->count(),
                'personas' => $this->personasDisponibles->take(5)->map(function($persona) { // Solo primeras 5 para log
                    return [
                        'id' => $persona->id,
                        'nombre_completo' => $persona->primer_nombre . ' ' . $persona->primer_apellido,
                        'numero_documento' => $persona->numero_documento,
                        'status' => $persona->status
                    ];
                })->toArray()
            ]);
            
            // Verificar también todas las personas activas para comparación
            $totalPersonasActivas = \App\Models\Persona::where('status', 1)->count();
            
            \Log::info('Total personas activas en sistema:', [
                'count' => $totalPersonasActivas
            ]);
            
            // Verificar aprendices actuales de esta ficha
            $totalAprendicesFicha = $this->selectedFicha->aprendices->count();
            
            \Log::info('Aprendices actuales de esta ficha:', [
                'ficha_id' => $this->selectedFicha->id,
                'count' => $totalAprendicesFicha,
                'aprendices' => $this->selectedFicha->aprendices->take(3)->map(function($aprendiz) { // Solo primeros 3 para log
                    return [
                        'id' => $aprendiz->id,
                        'persona_id' => $aprendiz->persona_id,
                        'nombre_completo' => $aprendiz->persona->primer_nombre . ' ' . $aprendiz->persona->primer_apellido,
                        'numero_documento' => $aprendiz->persona->numero_documento
                    ];
                })->toArray()
            ]);
            
            // Verificar la cuenta matemática
            \Log::info('Verificación matemática:', [
                'total_personas_activas' => $totalPersonasActivas,
                'aprendices_en_ficha' => $totalAprendicesFicha,
                'personas_disponibles_esperadas' => $totalPersonasActivas - $totalAprendicesFicha,
                'personas_disponibles_reales' => $this->personasDisponibles->count(),
                'diferencia' => ($totalPersonasActivas - $totalAprendicesFicha) - $this->personasDisponibles->count()
            ]);
            
            \Log::info('=== FIN CARGA PERSONAS DISPONIBLES ===');
        }
    }

    public function updatedSelectAllPersonas()
    {
        if ($this->selectAllPersonas) {
            $this->selectedPersonas = $this->personasDisponibles->pluck('id')->toArray();
        } else {
            $this->selectedPersonas = [];
        }
    }

    public function updatedSelectAllAprendicesAsignados()
    {
        if ($this->selectAllAprendicesAsignados) {
            $this->selectedAprendicesAsignados = $this->selectedFicha->aprendices->pluck('id')->toArray();
        } else {
            $this->selectedAprendicesAsignados = [];
        }
    }

    public function updatedSearchPersona()
    {
        // La búsqueda se filtra en la vista con el condicional
    }

    public function desasignarAprendices()
    {
        // Guardar el conteo antes de limpiar la selección
        $conteoAprendices = count($this->selectedAprendicesAsignados);
        
        if (empty($this->selectedAprendicesAsignados)) {
            $this->dispatch('notify', [
                'type' => 'warning', 
                'message' => 'Seleccione al menos un aprendiz para desasignar'
            ]);
            return;
        }

        try {
            \DB::beginTransaction();
            
            \Log::info('=== INICIO DESASIGNACIÓN APRENDICES ===');
            \Log::info('Aprendices a desasignar:', [
                'selectedAprendicesAsignados' => $this->selectedAprendicesAsignados,
                'count' => $conteoAprendices,
                'ficha_id' => $this->selectedFicha?->id
            ]);
            
            foreach ($this->selectedAprendicesAsignados as $aprendizId) {
                \Log::info('Desasignando aprendiz:', ['aprendiz_id' => $aprendizId]);
                
                // Eliminar el aprendiz (soft delete)
                $aprendiz = \App\Models\Aprendiz::find($aprendizId);
                if ($aprendiz) {
                    $aprendiz->delete();
                }
            }
            
            \DB::commit();
            
            \Log::info('Transacción confirmada - ' . $conteoAprendices . ' aprendices desasignados');
            
            // Recargar la ficha para actualizar los aprendices asignados
            $this->selectedFicha = \App\Models\FichaCaracterizacion::with(['aprendices.persona'])->find($this->selectedFicha->id);
            
            // Recargar personas disponibles (ahora disponibles los desasignados)
            $this->loadPersonasDisponibles();
            
            // Limpiar selección
            $this->reset(['selectedAprendicesAsignados', 'selectAllAprendicesAsignados']);
            
            // Forzar refresh de la vista
            $this->dispatch('refreshComponent');
            $this->dispatch('$refresh');
            
            $this->dispatch('notify', [
                'type' => 'success', 
                'message' => $conteoAprendices . ' aprendices desasignados exitosamente'
            ]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al desasignar aprendices:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'selectedAprendicesAsignados' => $this->selectedAprendicesAsignados,
                'ficha_id' => $this->selectedFicha?->id
            ]);
            $this->dispatch('notify', [
                'type' => 'error', 
                'message' => 'Error al desasignar aprendices: ' . $e->getMessage()
            ]);
        }
        
        \Log::info('=== FIN DESASIGNACIÓN APRENDICES ===');
    }

    public function updatedSelectedPersonas()
    {
        // Depuración para ver qué se está seleccionando
        \Log::info('Personas seleccionadas actualizadas:', [
            'selectedPersonas' => $this->selectedPersonas,
            'count' => count($this->selectedPersonas),
            'personas_disponibles_count' => count($this->personasDisponibles)
        ]);
    }

    public function asignarAprendices()
    {
        // Guardar el conteo antes de limpiar la selección
        $conteoPersonas = count($this->selectedPersonas);
        
        // Depuración antes de asignar
        \Log::info('=== INICIO ASIGNACIÓN APRENDICES ===');
        \Log::info('Datos antes de asignar:', [
            'selectedPersonas' => $this->selectedPersonas,
            'count' => $conteoPersonas,
            'ficha_id' => $this->selectedFicha?->id,
            'ficha_codigo' => $this->selectedFicha?->ficha
        ]);

        if (empty($this->selectedPersonas)) {
            \Log::info('No hay personas seleccionadas - abortando');
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'Seleccione al menos una persona para asignar']);
            return;
        }

        try {
            \DB::beginTransaction();
            
            \Log::info('Iniciando transacción DB para asignar aprendices');
            
            foreach ($this->selectedPersonas as $index => $personaId) {
                \Log::info('Asignando persona ' . ($index + 1) . ':', ['persona_id' => $personaId]);
                
                \App\Models\Aprendiz::create([
                    'persona_id' => $personaId,
                    'ficha_caracterizacion_id' => $this->selectedFicha->id,
                    'estado' => 1,
                    'user_create_id' => auth()->id(),
                    'user_edit_id' => auth()->id(),
                ]);
            }
            
            \DB::commit();
            
            \Log::info('Transacción confirmada - ' . $conteoPersonas . ' aprendices asignados');
            
            // Recargar la ficha para actualizar los aprendices asignados
            \Log::info('Recargando ficha con aprendices...');
            $this->selectedFicha = \App\Models\FichaCaracterizacion::with(['aprendices.persona'])->find($this->selectedFicha->id);
            
            \Log::info('Ficha recargada:', [
                'aprendices_count' => $this->selectedFicha?->aprendices->count(),
                'aprendices_ids' => $this->selectedFicha?->aprendices->pluck('id')->toArray()
            ]);
            
            // Recargar personas disponibles (excluyendo las ya asignadas)
            $this->loadPersonasDisponibles();
            
            // Limpiar selección
            $this->reset(['selectedPersonas', 'selectAllPersonas']);
            
            // Forzar refresh de la vista
            $this->dispatch('refreshComponent');
            $this->dispatch('$refresh');
            
            $this->dispatch('notify', [
                'type' => 'success', 
                'message' => $conteoPersonas . ' personas asignadas como aprendices exitosamente'
            ]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al asignar aprendices:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'selectedPersonas' => $this->selectedPersonas,
                'ficha_id' => $this->selectedFicha?->id
            ]);
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error al asignar aprendices: ' . $e->getMessage()]);
        }
        
        \Log::info('=== FIN ASIGNACIÓN APRENDICES ===');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedProgramaFilter()
    {
        $this->resetPage();
    }

    public function updatedRegionalFilter()
    {
        $this->resetPage();
    }

    public function updatedSedeFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = FichaCaracterizacion::with(['programaFormacion', 'sede', 'instructor.persona', 'ambiente', 'aprendices.persona'])
            ->when($this->search, function ($query) {
                $query->where('ficha', 'like', '%' . $this->search . '%')
                    ->orWhereHas('programaFormacion', function ($q) {
                        $q->where('nombre', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->programaFilter, function ($query) {
                $query->where('programa_formacion_id', $this->programaFilter);
            })
            ->when($this->regionalFilter, function ($query) {
                $query->whereHas('sede.regional', function ($q) {
                    $q->where('id', $this->regionalFilter);
                });
            })
            ->when($this->sedeFilter, function ($query) {
                $query->where('sede_id', $this->sedeFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('id', 'desc');

        $this->fichas = $query->paginate($this->perPage);

        return view('livewire.fichas.ficha-index', [
            'fichas' => $this->fichas,
            'programas' => $this->programas,
            'regionales' => $this->regionales,
            'sedes' => $this->sedes,
        ]);
    }

    #[On('showNotification')]
    public function showNotification($data)
    {
        // Este método es para el sistema de notificaciones
        // El JavaScript manejará la visualización
        // El evento puede venir como array o como parámetros separados
        if (is_array($data)) {
            $type = $data['type'] ?? 'info';
            $message = $data['message'] ?? 'Notificación';
        } else {
            // Si vienen como parámetros separados (compatibilidad)
            $args = func_get_args();
            $type = $args[0] ?? 'info';
            $message = $args[1] ?? 'Notificación';
        }
    }
}
