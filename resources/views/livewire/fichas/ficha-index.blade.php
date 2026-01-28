<div>
    <!-- Toast Minimalista ERP -->
    <div class="toast toast-minimal">
        <i class="toast-icon"></i>
        <span class="toast-text"></span>
    </div>

    <!-- Barra de herramientas moderna -->
    <div class="toolbar">
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   class="search-input" 
                   placeholder="Buscar por ficha, programa, sede...">
        </div>
        
        <!-- Filtros adicionales -->
        <div class="filters-container">
            <select wire:model.live="statusFilter" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="1">Activas</option>
                <option value="0">Inactivas</option>
            </select>
            
            <select wire:model.live="programaFilter" class="filter-select">
                <option value="">Todos los programas</option>
                @foreach($programas as $programa)
                    <option value="{{ $programa->id }}">{{ $programa->nombre }}</option>
                @endforeach
            </select>
            
            <select wire:model.live="regionalFilter" class="filter-select">
                <option value="">Todas las regionales</option>
                @foreach($regionales as $regional)
                    <option value="{{ $regional->id }}">{{ $regional->nombre }}</option>
                @endforeach
            </select>
            
            <select wire:model.live="sedeFilter" class="filter-select">
                <option value="">Todas las sedes</option>
                @foreach($sedes as $sede)
                    <option value="{{ $sede->id }}">{{ $sede->sede }}</option>
                @endforeach
            </select>
            
            @if ($search || $statusFilter !== '' || $programaFilter !== '' || $regionalFilter !== '' || $sedeFilter !== '')
                <button wire:click="clearFilters" class="btn-clear-filters">
                    <i class="fas fa-times"></i>
                </button>
            @endif
        </div>
        
        <div class="results-selector">
            <select wire:model.live="perPage" class="results-select">
                <option value="10">10 resultados</option>
                <option value="15">15 resultados</option>
                <option value="25">25 resultados</option>
                <option value="50">50 resultados</option>
            </select>
        </div>
        
        <div class="actions-container">
    <button wire:click="openCreateModal" class="btn-primary-modern">
        <i class="fas fa-plus"></i>
        Nueva Ficha
    </button>
</div>
    </div>

    <!-- Indicador de carga -->
    <div wire:loading wire:target="search" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Buscando...
    </div>

    <div wire:loading wire:target="statusFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por estado...
    </div>

    <div wire:loading wire:target="programaFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por programa...
    </div>

    <div wire:loading wire:target="regionalFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por regional...
    </div>

    <div wire:loading wire:target="sedeFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por sede...
    </div>

    <div wire:loading wire:target="perPage" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Actualizando resultados...
    </div>

    <!-- Tabla ERP -->
    <div class="table-scroll-wrapper">
        <table class="modern-table">
            <thead>
                <tr>
                    <th class="sortable codigo" wire:click="sortBy('created_at')">
                        #
                        @if ($sortField === 'created_at')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th class="sortable ficha" wire:click="sortBy('ficha')">
                        Ficha
                        @if ($sortField === 'ficha')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th class="programa">Programa</th>
                    <th class="instructor">Instructor Líder</th>
                    <th class="sede">Sede</th>
                    <th class="ambiente">Ambiente</th>
                    <th class="estado">Estado</th>
                    <th class="th-actions sticky-actions">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($fichas as $ficha)
                    <tr>
                        <td class="codigo">
                            <span class="badge-modern badge-primary">{{ ($fichas->currentPage() - 1) * $fichas->perPage() + $loop->iteration }}</span>
                        </td>
                        <td class="ficha fw-medium">
                            <span class="badge-modern badge-info">{{ $ficha->ficha }}</span>
                        </td>
                        <td class="programa">
                            @if($ficha->programaFormacion)
                                <span class="badge-modern badge-success">{{ $ficha->programaFormacion->nombre }}</span>
                            @else
                                <span class="badge-modern badge-warning">Sin programa</span>
                            @endif
                        </td>
                        <td class="instructor">
                            @if($ficha->instructor && $ficha->instructor->persona)
                                <div>
                                    <strong>{{ $ficha->instructor->persona->primer_nombre }} {{ $ficha->instructor->persona->primer_apellido }}</strong>
                                    <br><small class="text-muted">{{ $ficha->instructor->persona->numero_documento }}</small>
                                </div>
                            @else
                                <span class="badge-modern badge-secondary">Sin asignar</span>
                            @endif
                        </td>
                        <td class="sede">
                            @if($ficha->sede)
                                <span class="badge-modern badge-primary">{{ $ficha->sede->sede }}</span>
                            @else
                                <span class="badge-modern badge-secondary">Sin sede</span>
                            @endif
                        </td>
                        <td class="ambiente">
                            @if($ficha->ambiente)
                                <span class="badge-modern badge-info">{{ $ficha->ambiente->title }}</span>
                            @else
                                <span class="badge-modern badge-secondary">Sin ambiente</span>
                            @endif
                        </td>
                        <td class="estado">
                            <button wire:click="toggleStatus({{ $ficha->id }})"
                                wire:loading.attr="disabled"
                                class="badge-toggle {{ $ficha->status ? 'badge-success' : 'badge-danger' }}">
                                <i class="fas fa-sync-alt me-1"></i>
                                {{ $ficha->status ? 'Activa' : 'Inactiva' }}
                            </button>
                        </td>
                        <td class="td-actions sticky-actions">
    @if(auth()->user()->can('VER FICHA DE CARACTERIZACION'))
        <button wire:click="openShowModal({{ $ficha->id }})" 
                class="btn-action btn-view"
                title="Ver detalles">
            <i class="fas fa-eye"></i>
        </button>
    @endif
    
    @if(auth()->user()->can('EDITAR FICHA DE CARACTERIZACION'))
        <button wire:click="editFicha({{ $ficha->id }})" 
                class="btn-action btn-edit"
                title="Editar ficha">
            <i class="fas fa-edit"></i>
        </button>
    @endif
    
    @if(auth()->user()->can('GESTIONAR APRENDICES FICHA'))
        <button wire:click="openGestionarAprendicesDirect({{ $ficha->id }})" 
                class="btn-action btn-users"
                title="Gestionar aprendices">
            <i class="fas fa-users"></i>
        </button>
    @endif
    
    @if(auth()->user()->can('ELIMINAR FICHA DE CARACTERIZACION'))
        <button wire:click="openDeleteModal({{ $ficha->id }})" 
                class="btn-action btn-delete"
                title="Eliminar ficha">
            <i class="fas fa-trash"></i>
        </button>
    @endif
</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-folder-open"></i>
                                <h3>No hay fichas registradas</h3>
                                <p>
                                    @if($search || $statusFilter !== '' || $programaFilter !== '' || $regionalFilter !== '' || $sedeFilter !== '')
                                        No se encontraron resultados con los filtros aplicados.
                                    @else
                                        Comienza registrando una nueva ficha en el sistema.
                                    @endif
                                </p>
                                @if(!$search && $statusFilter === '' && $programaFilter === '' && $regionalFilter === '' && $sedeFilter === '')
                                    @can('CREAR FICHA CARACTERIZACION')
                                        <button wire:click="openCreateModal" class="btn-primary">
                                            <i class="fas fa-plus"></i>
                                            Nueva Ficha
                                        </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
        
        <!-- Paginación (siempre visible) -->
        <div class="pagination-wrapper">
            <div class="pagination-modern">
                <div class="pagination-info">
                    Mostrando {{ $fichas->firstItem() ?? 0 }} a {{ $fichas->lastItem() ?? 0 }} 
                    de {{ $fichas->total() }} resultados
                </div>
                <div class="pagination-links">
                    {{ $fichas->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>

    <!-- Modal Crear/Editar -->
    @if ($showCreateModal || $showEditModal)
        <div class="modal-overlay" wire:click="closeCreateEditModals">
            <div class="modal-container modal-xl" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $showCreateModal ? 'Crear Ficha' : 'Editar Ficha' }}
                    </h5>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    <livewire:fichas.ficha-form 
                        :ficha="$selectedFicha" 
                        :is-edit="$showEditModal" />
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Ver Detalles -->
    @if ($showShowModal && $selectedFicha)
        <div class="modal-overlay" wire:click="$set('showShowModal', false)">
            <div class="modal-container modal-lg" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        Ficha: {{ $selectedFicha->ficha }}
                    </h5>
                    <button class="modal-close" wire:click="$set('showShowModal', false)">✕</button>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    <!-- Sección: Información Básica -->
                    <div class="modal-section">
                        <h6 class="section-title">Información Básica</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Código de Ficha</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    <span class="badge-modern badge-info">{{ $selectedFicha->ficha }}</span>
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Programa de Formación</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedFicha->programaFormacion?->nombre ?? 'N/A' }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Estado</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    <span class="badge-modern {{ $selectedFicha->status ? 'badge-success' : 'badge-danger' }}">
                                        {{ $selectedFicha->status ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Asignaciones -->
                    <div class="modal-section">
                        <h6 class="section-title">Asignaciones</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Instructor Líder</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    @if($selectedFicha->instructor && $selectedFicha->instructor->persona)
                                        {{ $selectedFicha->instructor->persona->primer_nombre }} {{ $selectedFicha->instructor->persona->primer_apellido }}
                                    @else
                                        <span class="badge-modern badge-secondary">Sin asignar</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Sede</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedFicha->sede?->sede ?? 'N/A' }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Ambiente</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedFicha->ambiente?->title ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Días de Formación -->
                    <div class="modal-section">
                        <h6 class="section-title">Días de Formación</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div style="grid-column: 1 / -1;">
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 8px;">Días de la Semana</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    @php
                                        $diasSemana = [
                                            12 => 'LUNES',
                                            13 => 'MARTES', 
                                            14 => 'MIÉRCOLES',
                                            15 => 'JUEVES',
                                            16 => 'VIERNES',
                                            17 => 'SÁBADO',
                                            18 => 'DOMINGO',
                                        ];
                                        
                                        $diasFicha = \App\Models\FichaDiasFormacion::where('ficha_id', $selectedFicha->id)
                                            ->pluck('dia_id')
                                            ->toArray();
                                    @endphp
                                    
                                    @if(!empty($diasFicha))
                                        @foreach($diasFicha as $diaId)
                                            @if(isset($diasSemana[$diaId]))
                                                <span class="badge-modern badge-info" style="margin-right: 4px; margin-bottom: 4px;">
                                                    {{ $diasSemana[$diaId] }}
                                                </span>
                                            @endif
                                        @endforeach
                                    @else
                                        <span class="badge-modern badge-secondary">Sin días asignados</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Estadísticas -->
                    <div class="modal-section">
                        <h6 class="section-title">Estadísticas</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Número de Aprendices</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    <span class="badge-modern badge-primary">{{ $selectedFicha->aprendices_count ?? 0 }}</span>
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Fecha de Creación</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedFicha->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Última Actualización</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedFicha->updated_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="modal-footer">
                    <button class="btn-modal btn-secondary" wire:click="$set('showShowModal', false)">
                        <i class="fas fa-times"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Gestionar Aprendices -->
    @if ($showGestionarAprendicesModal && $selectedFicha)
        <div class="modal-overlay" wire:click="closeGestionarAprendicesModal">
            <div class="modal-container modal-xl" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-users mr-2"></i>
                        Gestionar Aprendices - Ficha: {{ $selectedFicha->ficha }}
                    </h5>
                    <button class="modal-close" wire:click="closeGestionarAprendicesModal">✕</button>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    <!-- Sección: Aprendices Asignados -->
                    <div class="modal-section">
                        <h6 class="section-title">Aprendices Asignados</h6>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <!-- Tabla de aprendices asignados -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-user-check mr-2"></i> 
                                        Lista de Aprendices
                                    </h6>
                                    <span class="badge-modern badge-info">{{ $selectedFicha->aprendices->count() ?? 0 }}</span>
                                </div>
                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                    @if($selectedFicha->aprendices->count() > 0)
                                        <table class="table table-hover table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="40px">
                                                        <input type="checkbox" wire:model.live="selectAllAprendicesAsignados" class="form-check-input">
                                                    </th>
                                                    <th>Documento</th>
                                                    <th>Nombre Completo</th>
                                                    <th>Correo</th>
                                                    <th>Teléfono</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($selectedFicha->aprendices ?? [] as $aprendiz)
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="checkbox" 
                                                                    wire:model.live="selectedAprendicesAsignados" 
                                                                    value="{{ $aprendiz->id }}" 
                                                                    class="form-check-input">
                                                        </td>
                                                        <td>{{ $aprendiz->persona->numero_documento }}</td>
                                                        <td>
                                                            <strong>
                                                                {{ $aprendiz->persona->primer_nombre }} {{ $aprendiz->persona->primer_apellido }}
                                                                @if($aprendiz->persona->segundo_nombre)
                                                                    {{ $aprendiz->persona->segundo_nombre }}
                                                                @endif
                                                                @if($aprendiz->persona->segundo_apellido)
                                                                    {{ $aprendiz->persona->segundo_apellido }}
                                                                @endif
                                                            </strong>
                                                        </td>
                                                        <td>{{ $aprendiz->persona->email ?? 'N/A' }}</td>
                                                        <td>{{ $aprendiz->persona->telefono ?? 'N/A' }}</td>
                                                        <td>
                                                            @if($aprendiz->estado)
                                                                <span class="badge-modern badge-success">Activo</span>
                                                            @else
                                                                <span class="badge-modern badge-danger">Inactivo</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="empty-state">
                                            <i class="fas fa-users"></i>
                                            <h4>No hay aprendices asignados</h4>
                                            <p>Utiliza el panel de la derecha para asignar aprendices.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Panel de personas disponibles -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-user-plus mr-2"></i> 
                                        Personas Disponibles
                                    </h6>
                                    <span class="badge-modern badge-success">{{ $personasDisponibles->count() ?? 0 }}</span>
                                </div>
                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                    @if(($personasDisponibles->count() ?? 0) > 0)
                                        <!-- Búsqueda -->
                                        <div class="mb-3">
                                            <div class="input-group">
                                                <input type="text" class="form-control" placeholder="Buscar persona..." wire:model.live="searchPersona">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-search"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Tabla -->
                                        <table class="table table-hover table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" width="40px">
                                                        <input type="checkbox" 
                                                                wire:model.live="selectAllPersonas" 
                                                                class="form-check-input">
                                                    </th>
                                                    <th>Documento</th>
                                                    <th>Nombre</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($personasDisponibles as $persona)
                                                    @if(!empty($searchPersona) ? str_contains(strtolower($persona->primer_nombre . ' ' . $persona->primer_apellido . ' ' . $persona->numero_documento), strtolower($searchPersona)) : true)
                                                        <tr>
                                                            <td class="text-center">
                                                                <input type="checkbox" 
                                                                        wire:model.live="selectedPersonas" 
                                                                        value="{{ $persona->id }}" 
                                                                        class="form-check-input">
                                                            </td>
                                                            <td>{{ $persona->numero_documento }}</td>
                                                            <td>
                                                                {{ $persona->primer_nombre }} {{ $persona->primer_apellido }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="empty-state">
                                            <i class="fas fa-user-slash"></i>
                                            <h4>No hay personas disponibles</h4>
                                            <p>Todas las personas ya son aprendices o están asignadas.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Acciones -->
                    @if(($personasDisponibles->count() ?? 0) > 0 || ($selectedFicha->aprendices->count() ?? 0) > 0)
                        <div class="modal-section">
                            <h6 class="section-title">Acciones</h6>
                            <div style="display: grid; gap: 16px;">
                                <!-- Información de selección -->
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                                    <div>
                                        <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Personas Seleccionadas</div>
                                        <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                            <span class="badge-modern badge-primary">{{ count($selectedPersonas) }}</span> de 
                                            <span class="badge-modern badge-info">{{ $personasDisponibles->count() }}</span> disponibles
                                        </div>
                                    </div>
                                    @if(($selectedFicha->aprendices->count() ?? 0) > 0)
                                        <div>
                                            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Aprendices Seleccionados</div>
                                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                                <span class="badge-modern badge-warning">{{ count($selectedAprendicesAsignados) }}</span> de 
                                                <span class="badge-modern badge-info">{{ $selectedFicha->aprendices->count() }}</span> asignados
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Botones de acción -->
                                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                                    @if(count($selectedAprendicesAsignados) > 0)
                                        <button wire:click="desasignarAprendices" class="btn-modal btn-danger">
                                            <i class="fas fa-user-minus mr-2"></i>
                                            Desasignar Seleccionadas
                                        </button>
                                    @endif
                                    @if(count($selectedPersonas) > 0)
                                        <button wire:click="asignarAprendices" class="btn-modal btn-success">
                                            <i class="fas fa-user-plus mr-2"></i>
                                            Asignar Seleccionadas
                                        </button>
                                    @else
                                        <button class="btn-modal btn-success" disabled>
                                            <i class="fas fa-user-plus mr-2"></i>
                                            Asignar Seleccionadas
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Footer -->
                <div class="modal-footer">
                    <!-- Footer vacío - solo el botón de cerrar en el header -->
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Confirmación Eliminación -->
    @if ($showDeleteModal && $selectedFicha)
        <div class="modal-overlay" wire:click="closeDeleteModal">
            <div class="modal-container" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Ficha</h5>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    <!-- Alerta de advertencia -->
                    <div class="modal-alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Esta acción es permanente y no se puede deshacer.</span>
                    </div>
                    
                    <!-- Información del elemento -->
                    <div class="modal-section">
                        <h6 class="section-title">Información de la Ficha</h6>
                        <div style="display: grid; gap: 8px;">
                            <div><strong>Ficha:</strong> {{ $selectedFicha->ficha }}</div>
                            <div><strong>Programa:</strong> {{ $selectedFicha->programaFormacion?->nombre ?? 'N/A' }}</div>
                            <div><strong>Sede:</strong> {{ $selectedFicha->sede?->sede ?? 'N/A' }}</div>
                        </div>
                    </div>
                    
                    @if($selectedFicha->aprendices_count > 0)
                        <div class="modal-alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Esta ficha tiene {{ $selectedFicha->aprendices_count }} aprendices asignados. No se puede eliminar.</span>
                        </div>
                    @endif
                    
                    <!-- Mensaje de confirmación -->
                    <div class="modal-section">
                        <p style="margin: 0; color: #6b7280; font-size: 14px;">
                            ¿Está seguro de que desea eliminar esta ficha?
                        </p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="modal-footer">
                    <button class="btn-modal btn-secondary" wire:click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button class="btn-modal btn-danger" wire:click="deleteFicha({{ $selectedFicha->id }})" 
                            wire:loading.attr="disabled"
                            @if($selectedFicha->aprendices_count > 0) disabled @endif>
                        <i wire:loading.remove wire:target="deleteFicha" class="fas fa-trash"></i>
                        <span wire:loading.remove wire:target="deleteFicha">Eliminar</span>
                        <i wire:loading wire:target="deleteFicha" class="fas fa-spinner fa-spin"></i>
                        <span wire:loading wire:target="deleteFicha">Eliminando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
