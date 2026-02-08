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
use Illuminate\Support\Facades\Auth;

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
    
    // Propiedades para gestión de instructores
    public $showGestionarInstructoresModal = false;
    public $selectedFichaInstructores = null;
    public $instructoresDisponibles = [];
    public $selectedInstructores = [];
    public $selectAllInstructores = false;
    public $searchInstructor = '';
    public $instructoresAsignados = [];
    public $selectedInstructoresAsignados = [];
    public $selectAllInstructoresAsignados = false;
    
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
        ])
            ->withCount('aprendices')
            ->find($fichaId);
        
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
        ])
            ->withCount('aprendices')
            ->find($fichaId);
        
        if ($this->selectedFicha) {
            $this->showShowModal = true;
        }
    }

    public function openDeleteModal($fichaId)
    {
        $this->selectedFicha = FichaCaracterizacion::withCount('aprendices')->find($fichaId);
        
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
        $this->selectedFicha = FichaCaracterizacion::with(['programaFormacion', 'sede', 'instructor.persona', 'ambiente', 'aprendices.persona'])
            ->withCount('aprendices')
            ->find($fichaId);
        
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
            
            // Obtener IDs de personas relacionadas con esta ficha
            $personasRelacionadasIds = [];
            
            // 1. Aprendices activos en esta ficha
            $aprendicesIds = $this->selectedFicha->aprendices()
                ->where('estado', 1)
                ->pluck('persona_id')
                ->toArray();
            
            // 2. Instructor líder de la ficha
            $instructorLiderId = $this->selectedFicha->instructor ? $this->selectedFicha->instructor->persona_id : null;
            
            // 3. Instructores asignados a la ficha
            $instructoresAsignadosIds = $this->selectedFicha->instructorFicha()
                ->with('instructor.persona')
                ->get()
                ->pluck('instructor.persona_id')
                ->toArray();
            
            // Combinar todos los IDs relacionados
            $personasRelacionadasIds = array_merge($aprendicesIds, [$instructorLiderId], $instructoresAsignadosIds);
            $personasRelacionadasIds = array_filter($personasRelacionadasIds); // Eliminar nulos
            $personasRelacionadasIds = array_unique($personasRelacionadasIds); // Eliminar duplicados
            
            \Log::info('Personas relacionadas con esta ficha:', [
                'aprendices_ids' => $aprendicesIds,
                'instructor_lider_id' => $instructorLiderId,
                'instructores_asignados_ids' => $instructoresAsignadosIds,
                'todos_ids' => $personasRelacionadasIds,
                'total_relacionados' => count($personasRelacionadasIds)
            ]);
            
            // Obtener personas que NO son aprendices en NINGUNA ficha
            // Y que NO están relacionadas con esta ficha específica
            $this->personasDisponibles = \App\Models\Persona::where('status', 1)
                ->whereNotIn('id', function ($query) {
                    $query->select('persona_id')
                        ->from('aprendices')
                        ->where('estado', 1); // Solo aprendices activos en cualquier ficha
                })
                ->whereNotIn('id', $personasRelacionadasIds) // Excluir personas relacionadas con esta ficha
                ->orderBy('primer_nombre')
                ->orderBy('primer_apellido')
                ->get();
            
            \Log::info('Personas disponibles cargadas (personas que NO son aprendices en ninguna ficha Y que NO están relacionadas con esta ficha):', [
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
            
            // Verificar aprendices activos en TODAS las fichas
            $totalAprendicesActivos = \App\Models\Aprendiz::where('estado', 1)->count();
            
            \Log::info('Total aprendices activos en todas las fichas:', [
                'count' => $totalAprendicesActivos
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
                'total_aprendices_activos' => $totalAprendicesActivos,
                'personas_relacionadas_ficha' => count($personasRelacionadasIds),
                'personas_disponibles_esperadas' => $totalPersonasActivas - $totalAprendicesActivos - count($personasRelacionadasIds),
                'personas_disponibles_reales' => $this->personasDisponibles->count(),
                'diferencia' => ($totalPersonasActivas - $totalAprendicesActivos - count($personasRelacionadasIds)) - $this->personasDisponibles->count()
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

    // Métodos para gestión de instructores
    public function openGestionarInstructoresDirect($fichaId)
    {
        \Log::info('=== INICIO GESTIÓN INSTRUCTORES ===');
        \Log::info('Abriendo gestión de instructores - Ficha ID: ' . $fichaId);
        
        // Cargar la ficha con todas las relaciones necesarias como lo hace el controlador original
        $this->selectedFichaInstructores = \App\Models\FichaCaracterizacion::with([
            'instructor.persona',
            'instructorFicha.instructor.persona',
            'instructorFicha.instructorFichaDias.dia',
            'diasFormacion.dia',
            'programaFormacion.redConocimiento',
            'sede.regional',
            'jornadaFormacion.parametro'
        ])->find($fichaId);
        
        if ($this->selectedFichaInstructores) {
            \Log::info('Ficha cargada para gestión de instructores:', [
                'ficha_id' => $this->selectedFichaInstructores->id,
                'ficha_codigo' => $this->selectedFichaInstructores->ficha,
                'programa' => $this->selectedFichaInstructores->programaFormacion->nombre ?? 'N/A',
                'instructores_asignados_count' => $this->selectedFichaInstructores->instructorFicha->count()
            ]);
            
            // Obtener instructores ya asignados a esta ficha como lo hace el controlador
            $this->instructoresAsignados = $this->selectedFichaInstructores->instructorFicha()
                ->with(['competencia', 'resultadosAprendizaje'])
                ->with(['instructor.persona', 'instructorFichaDias.dia'])
                ->get();
            
            // Cargar instructores disponibles
            $this->loadInstructoresDisponibles();
            
            // Resetear selecciones
            $this->reset(['selectedInstructores', 'selectAllInstructores', 'searchInstructor', 'selectedInstructoresAsignados', 'selectAllInstructoresAsignados']);
            
            $this->showGestionarInstructoresModal = true;
            
            \Log::info('Modal de gestión de instructores abierta');
        } else {
            \Log::error('No se pudo cargar la ficha para gestión de instructores');
            $this->dispatch('notify', [
                'type' => 'error', 
                'message' => 'No se pudo cargar la información de la ficha'
            ]);
        }
        
        \Log::info('=== FIN GESTIÓN INSTRUCTORES ===');
    }

    public function closeGestionarInstructoresModal()
    {
        $this->showGestionarInstructoresModal = false;
        $this->reset([
            'selectedFichaInstructores', 
            'instructoresDisponibles', 
            'selectedInstructores', 
            'selectAllInstructores', 
            'searchInstructor',
            'instructoresAsignados',
            'selectedInstructoresAsignados',
            'selectAllInstructoresAsignados'
        ]);
    }

    private function loadInstructoresDisponibles()
    {
        if ($this->selectedFichaInstructores) {
            \Log::info('=== CARGANDO INSTRUCTORES DISPONIBLES ===');
            \Log::info('Ficha seleccionada:', [
                'ficha_id' => $this->selectedFichaInstructores->id, 
                'ficha_codigo' => $this->selectedFichaInstructores->ficha
            ]);
            
            // Obtener instructores que no están asignados a esta ficha
            // Usando la misma lógica que el controlador original
            $instructoresAsignadosIds = $this->selectedFichaInstructores->instructorFicha->pluck('instructor_id')->toArray();
            
            $this->instructoresDisponibles = \App\Models\Instructor::whereHas('persona', function($query) {
                    $query->where('status', 1);
                })
                ->whereNotIn('instructors.id', $instructoresAsignadosIds) // Especificar la tabla explícitamente
                ->with('persona')
                ->ordenarPorNombre() // Usar el scope del modelo para ordenar
                ->get();
            
            \Log::info('Instructores disponibles cargados:', [
                'count' => $this->instructoresDisponibles->count(),
                'instructores' => $this->instructoresDisponibles->take(5)->map(function($instructor) {
                    return [
                        'id' => $instructor->id,
                        'nombre_completo' => $instructor->persona->primer_nombre . ' ' . $instructor->persona->primer_apellido,
                        'numero_documento' => $instructor->persona->numero_documento,
                        'tipo_documento' => $instructor->persona->tipo_documento
                    ];
                })->toArray()
            ]);
            
            // Verificar también todos los instructores activos para comparación
            $totalInstructoresActivos = \App\Models\Instructor::whereHas('persona', function($query) {
                $query->where('status', 1);
            })->count();
            
            \Log::info('Total instructores activos en sistema:', [
                'count' => $totalInstructoresActivos
            ]);
            
            // Verificar instructores actuales de esta ficha
            $totalInstructoresFicha = $this->selectedFichaInstructores->instructorFicha->count();
            
            \Log::info('Instructores actuales de esta ficha:', [
                'ficha_id' => $this->selectedFichaInstructores->id,
                'count' => $totalInstructoresFicha,
                'instructores' => $this->selectedFichaInstructores->instructorFicha->take(3)->map(function($asignacion) {
                    return [
                        'id' => $asignacion->id,
                        'instructor_id' => $asignacion->instructor_id,
                        'nombre_completo' => $asignacion->instructor->persona->primer_nombre . ' ' . $asignacion->instructor->persona->primer_apellido,
                        'numero_documento' => $asignacion->instructor->persona->numero_documento
                    ];
                })->toArray()
            ]);
            
            // Verificar la cuenta matemática
            \Log::info('Verificación matemática:', [
                'total_instructores_activos' => $totalInstructoresActivos,
                'instructores_en_ficha' => $totalInstructoresFicha,
                'instructores_disponibles_esperados' => $totalInstructoresActivos - $totalInstructoresFicha,
                'instructores_disponibles_reales' => $this->instructoresDisponibles->count(),
                'diferencia' => ($totalInstructoresActivos - $totalInstructoresFicha) - $this->instructoresDisponibles->count()
            ]);
            
            \Log::info('=== FIN CARGA INSTRUCTORES DISPONIBLES ===');
        }
    }

    public function updatedSelectAllInstructores()
    {
        if ($this->selectAllInstructores) {
            $this->selectedInstructores = $this->instructoresDisponibles->pluck('id')->toArray();
        } else {
            $this->selectedInstructores = [];
        }
    }

    public function updatedSelectAllInstructoresAsignados()
    {
        if ($this->selectAllInstructoresAsignados) {
            $this->selectedInstructoresAsignados = $this->selectedFichaInstructores->instructorFicha->pluck('id')->toArray();
        } else {
            $this->selectedInstructoresAsignados = [];
        }
    }

    public function updatedSearchInstructor()
    {
        // La búsqueda se filtra en la vista con el condicional
    }

    public function asignarInstructores()
    {
        // Guardar el conteo antes de limpiar la selección
        $conteoInstructores = count($this->selectedInstructores);
        
        \Log::info('=== INICIO ASIGNACIÓN INSTRUCTORES ===');
        \Log::info('Datos antes de asignar:', [
            'selectedInstructores' => $this->selectedInstructores,
            'count' => $conteoInstructores,
            'ficha_id' => $this->selectedFichaInstructores?->id
        ]);

        if (empty($this->selectedInstructores)) {
            \Log::info('No hay instructores seleccionados - abortando');
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'Seleccione al menos un instructor para asignar']);
            return;
        }

        try {
            \DB::beginTransaction();
            
            \Log::info('Iniciando transacción DB para asignar instructores');
            
            // Preparar datos para cada instructor seleccionado
            $instructoresData = [];
            foreach ($this->selectedInstructores as $index => $instructorId) {
                \Log::info('Asignando instructor ' . ($index + 1) . ':', ['instructor_id' => $instructorId]);
                
                $instructoresData[] = [
                    'instructor_id' => $instructorId,
                    'ficha_caracterizacion_id' => $this->selectedFichaInstructores->id,
                    'fecha_inicio' => $this->selectedFichaInstructores->fecha_inicio,
                    'fecha_fin' => $this->selectedFichaInstructores->fecha_fin,
                    'total_horas_instructor' => $this->selectedFichaInstructores->total_horas ?? 0,
                    'estado' => 1,
                    'user_create_id' => auth()->id(),
                    'user_edit_id' => auth()->id(),
                ];
            }
            
            // Usar el servicio especializado para la asignación
            $asignacionService = app(\App\Services\AsignacionInstructorService::class);
            $resultado = $asignacionService->asignarInstructores(
                $instructoresData,
                $this->selectedFichaInstructores->id,
                $this->selectedFichaInstructores->instructor_id ?? null,
                auth()->id()
            );
            
            \DB::commit();
            
            \Log::info('Transacción confirmada - ' . $conteoInstructores . ' instructores asignados');
            \Log::info('Resultado del servicio:', $resultado);
            
            // Recargar la ficha para actualizar los instructores asignados
            $this->selectedFichaInstructores = \App\Models\FichaCaracterizacion::with([
                'instructor.persona',
                'instructorFicha.instructor.persona',
                'instructorFicha.instructorFichaDias.dia',
                'diasFormacion.dia',
                'programaFormacion.redConocimiento',
                'sede.regional',
                'jornadaFormacion.parametro'
            ])->find($this->selectedFichaInstructores->id);
            
            // Recargar instructores asignados
            $this->instructoresAsignados = $this->selectedFichaInstructores->instructorFicha()
                ->with(['competencia', 'resultadosAprendizaje'])
                ->with(['instructor.persona', 'instructorFichaDias.dia'])
                ->get();
            
            // Recargar instructores disponibles
            $this->loadInstructoresDisponibles();
            
            // Limpiar selección
            $this->reset(['selectedInstructores', 'selectAllInstructores']);
            
            // Forzar refresh de la vista
            $this->dispatch('refreshComponent');
            $this->dispatch('$refresh');
            
            $this->dispatch('notify', [
                'type' => 'success', 
                'message' => $conteoInstructores . ' instructores asignados exitosamente'
            ]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al asignar instructores:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'selectedInstructores' => $this->selectedInstructores,
                'ficha_id' => $this->selectedFichaInstructores?->id
            ]);
            $this->dispatch('notify', [
                'type' => 'error', 
                'message' => 'Error al asignar instructores: ' . $e->getMessage()
            ]);
        }
        
        \Log::info('=== FIN ASIGNACIÓN INSTRUCTORES ===');
    }

    public function desasignarInstructores()
    {
        // Guardar el conteo antes de limpiar la selección
        $conteoInstructores = count($this->selectedInstructoresAsignados);
        
        if (empty($this->selectedInstructoresAsignados)) {
            $this->dispatch('notify', [
                'type' => 'warning', 
                'message' => 'Seleccione al menos un instructor para desasignar'
            ]);
            return;
        }

        try {
            \DB::beginTransaction();
            
            \Log::info('=== INICIO DESASIGNACIÓN INSTRUCTORES ===');
            \Log::info('Instructores a desasignar:', [
                'selectedInstructoresAsignados' => $this->selectedInstructoresAsignados,
                'count' => $conteoInstructores,
                'ficha_id' => $this->selectedFichaInstructores?->id
            ]);
            
            foreach ($this->selectedInstructoresAsignados as $asignacionId) {
                \Log::info('Desasignando asignación:', ['asignacion_id' => $asignacionId]);
                
                // Eliminar la asignación del instructor
                $asignacion = \App\Models\InstructorFichaCaracterizacion::find($asignacionId);
                if ($asignacion) {
                    // Verificar que no sea el instructor principal
                    if ($this->selectedFichaInstructores->instructor_id == $asignacion->instructor_id) {
                        \Log::warning('No se puede desasignar al instructor principal:', [
                            'asignacion_id' => $asignacionId,
                            'instructor_id' => $asignacion->instructor_id
                        ]);
                        continue;
                    }
                    
                    $asignacion->delete();
                }
            }
            
            \DB::commit();
            
            \Log::info('Transacción confirmada - ' . $conteoInstructores . ' instructores desasignados');
            
            // Recargar la ficha para actualizar los instructores asignados
            $this->selectedFichaInstructores = \App\Models\FichaCaracterizacion::with([
                'instructor.persona',
                'instructorFicha.instructor.persona',
                'instructorFicha.instructorFichaDias.dia',
                'diasFormacion.dia',
                'programaFormacion.redConocimiento',
                'sede.regional',
                'jornadaFormacion.parametro'
            ])->find($this->selectedFichaInstructores->id);
            
            // Recargar instructores asignados
            $this->instructoresAsignados = $this->selectedFichaInstructores->instructorFicha()
                ->with(['competencia', 'resultadosAprendizaje'])
                ->with(['instructor.persona', 'instructorFichaDias.dia'])
                ->get();
            
            // Recargar instructores disponibles
            $this->loadInstructoresDisponibles();
            
            // Limpiar selección
            $this->reset(['selectedInstructoresAsignados', 'selectAllInstructoresAsignados']);
            
            // Forzar refresh de la vista
            $this->dispatch('refreshComponent');
            $this->dispatch('$refresh');
            
            $this->dispatch('notify', [
                'type' => 'success', 
                'message' => $conteoInstructores . ' instructores desasignados exitosamente'
            ]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al desasignar instructores:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'selectedInstructoresAsignados' => $this->selectedInstructoresAsignados,
                'ficha_id' => $this->selectedFichaInstructores?->id
            ]);
            $this->dispatch('notify', [
                'type' => 'error', 
                'message' => 'Error al desasignar instructores: ' . $e->getMessage()
            ]);
        }
        
        \Log::info('=== FIN DESASIGNACIÓN INSTRUCTORES ===');
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
        $user = Auth::user();
        $roleNames = $user?->getRoleNames() ?? collect();
        $isOnlyInstructor = $user && $user->hasRole('INSTRUCTOR') && $roleNames->count() === 1;

        $instructorId = null;
        if ($isOnlyInstructor) {
            $instructorId = \App\Models\Instructor::where('persona_id', $user->persona_id)->value('id');
        }

        $query = FichaCaracterizacion::with(['programaFormacion', 'sede', 'instructor.persona', 'ambiente', 'aprendices.persona'])
            ->withCount('aprendices')
            ->when($isOnlyInstructor, function ($query) use ($instructorId) {
                if (!$instructorId) {
                    $query->whereRaw('1 = 0');
                    return;
                }

                // Para INSTRUCTOR (solo rol), mostrar únicamente fichas activas
                $query->where('status', 1);

                $query->where(function ($q) use ($instructorId) {
                    $q->where('instructor_id', $instructorId)
                        ->orWhereHas('instructorFicha', function ($sub) use ($instructorId) {
                            $sub->where('instructor_id', $instructorId);
                        });
                });
            })
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
            ->when(!$isOnlyInstructor && $this->statusFilter !== '', function ($query) {
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
