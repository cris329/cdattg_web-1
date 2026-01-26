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
                   placeholder="Buscar por documento, nombre...">
        </div>
        
        <!-- Filtros adicionales -->
        <div class="filters-container">
            <select wire:model.live="statusFilter" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
            
            <select wire:model.live="especialidadFilter" class="filter-select">
                <option value="">Todas las especialidades</option>
                @foreach($especialidades as $id => $nombre)
                    <option value="{{ $id }}">{{ $nombre }}</option>
                @endforeach
            </select>
            
            <select wire:model.live="regionalFilter" class="filter-select">
                <option value="">Todas las regionales</option>
                @foreach($regionales as $id => $nombre)
                    <option value="{{ $id }}">{{ $nombre }}</option>
                @endforeach
            </select>
            
            @if ($search || $statusFilter !== '' || $especialidadFilter !== '' || $regionalFilter !== '')
                <button wire:click="limpiarFiltros" class="btn-clear-filters" title="Limpiar filtros">
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
        
        @can('CREAR INSTRUCTOR')
            <button wire:click="openCreateModal" class="btn-primary-modern">
                <i class="fas fa-plus"></i>
                Nuevo Instructor
            </button>
        @endcan
    </div>

    <!-- Indicadores de carga -->
    <div wire:loading wire:target="search" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Buscando...
    </div>

    <div wire:loading wire:target="statusFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por estado...
    </div>

    <div wire:loading wire:target="especialidadFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por especialidad...
    </div>

    <div wire:loading wire:target="regionalFilter" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Filtrando por regional...
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
                    <th class="sortable nombre" wire:click="sortBy('nombre')">
                        Nombre
                        @if ($sortField === 'nombre')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} sort-icon"></i>
                        @endif
                    </th>
                    <th class="documento">Documento</th>
                    <th class="especialidades">Especialidades</th>
                    <th class="regional">Regional</th>
                    <th class="estado">Estado</th>
                    <th class="th-actions sticky-actions">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($instructores as $instructor)
                    <tr>
                        <td class="codigo">
                            <span class="badge-modern badge-primary">{{ $loop->iteration }}</span>
                        </td>
                        <td class="nombre fw-medium">
                            {{ $instructor->persona->primer_nombre }} 
                            {{ $instructor->persona->segundo_nombre ?? '' }}
                            {{ $instructor->persona->primer_apellido }}
                            {{ $instructor->persona->segundo_apellido ?? '' }}
                        </td>
                        <td class="documento">{{ $instructor->persona->numero_documento }}</td>
                        <td class="especialidades">
                            @php
                                $especialidades = $this->obtenerEspecialidadesFormateadas($instructor);
                            @endphp
                            
                            @if($especialidades['principal'])
                                <span class="badge-modern badge-primary">
                                    {{ $especialidades['principal'] }}
                                </span>
                            @endif
                            
                            @foreach(array_slice($especialidades['secundarias'], 0, 2) as $especialidad)
                                <span class="badge-modern badge-info">
                                    {{ $especialidad }}
                                </span>
                            @endforeach
                            
                            @if(count($especialidades['secundarias']) > 2)
                                <span class="badge-modern badge-secondary">
                                    +{{ count($especialidades['secundarias']) - 2 }}
                                </span>
                            @endif
                            
                            @if(!$especialidades['principal'] && count($especialidades['secundarias']) === 0)
                                <span class="badge-modern badge-secondary">Sin especialidades</span>
                            @endif
                        </td>
                        <td class="regional">{{ $instructor->regional->nombre ?? 'N/A' }}</td>
                        <td class="estado">
                            <button
                                wire:click="toggleStatus({{ $instructor->id }})"
                                wire:loading.attr="disabled"
                                class="badge-toggle {{ $instructor->status ? 'badge-success' : 'badge-danger' }}"
                                title="Cambiar estado">
                                <i class="fas fa-sync-alt me-1"></i>
                                {{ $instructor->status ? 'Activo' : 'Inactivo' }}
                            </button>
                        </td>
                        <td class="td-actions sticky-actions">
                            @can('VER INSTRUCTOR')
                                <button wire:click="openShowModal({{ $instructor->id }})" 
                                        class="btn-action btn-view" 
                                        title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endcan
                            
                            @can('GESTIONAR ESPECIALIDADES INSTRUCTOR')
                                <button wire:click="openEspecialidadesModal({{ $instructor->id }})" 
                                        class="btn-action btn-relations" 
                                        title="Gestionar especialidades">
                                    <i class="fas fa-graduation-cap"></i>
                                </button>
                            @endcan
                            
                            @can('VER FICHAS ASIGNADAS')
                                <button wire:click="openFichasModal({{ $instructor->id }})" 
                                        class="btn-action btn-relations" 
                                        title="Ver fichas asignadas">
                                    <i class="fas fa-clipboard-list"></i>
                                </button>
                            @endcan
                            
                            @can('EDITAR INSTRUCTOR')
                                <button wire:click="openEditModal({{ $instructor->id }})" 
                                        class="btn-action btn-edit" 
                                        title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endcan
                            
                            @can('ELIMINAR INSTRUCTOR')
                                <button wire:click="openDeleteModal({{ $instructor->id }})" 
                                        class="btn-action btn-delete" 
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <h3>No hay instructores registrados</h3>
                                <p>
                                    @if($search || $statusFilter !== '' || $especialidadFilter !== '' || $regionalFilter !== '')
                                        No hay resultados con los filtros aplicados. Intenta ajustar los criterios de búsqueda.
                                    @else
                                        Comienza creando el primer instructor del sistema.
                                    @endif
                                </p>
                                @if(!$search && $statusFilter === '' && $especialidadFilter === '' && $regionalFilter === '')
                                    @can('CREAR INSTRUCTOR')
                                        <button wire:click="openCreateModal" class="btn-primary-modern-sm" style="margin-top: 1rem;">
                                            <i class="fas fa-plus"></i>
                                            Crear Primer Instructor
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

    <!-- Paginación -->
    <div class="pagination-container">
        <div class="pagination-info">
            Mostrando {{ $instructores->firstItem() ?? 0 }} - {{ $instructores->lastItem() ?? 0 }} 
            de {{ $instructores->total() }} instructores
        </div>
        {{ $instructores->links() }}
    </div>

    <!-- Modal Crear/Editar Instructor -->
    @if ($showCreateModal || ($showEditModal && $selectedInstructor))
        <div class="modal-overlay" wire:click="$set('showCreateModal', false); $set('showEditModal', false);">
            <div class="modal-container modal-xl" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $showCreateModal ? 'Crear Instructor' : 'Editar Instructor' }}
                    </h5>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    @if ($showCreateModal)
                        <livewire:instructores.instructor-form :isEdit="false" />
                    @endif
                    @if ($showEditModal && $selectedInstructor)
                        <livewire:instructores.instructor-form :isEdit="true" :instructor="$selectedInstructor" />
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Ver Detalles -->
    @if ($showShowModal && $selectedInstructor)
        <div class="modal-overlay" wire:click="$set('showShowModal', false)">
            <div class="modal-container modal-lg" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">{{ $selectedInstructor->persona->nombre_completo }}</h5>
                    <button class="modal-close" wire:click="$set('showShowModal', false)">✕</button>
                </div>
                
                <!-- Body -->
                <div class="modal-body">
                    <!-- Sección: Información Personal -->
                    <div class="modal-section">
                        <h6 class="section-title">Información Personal</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Tipo Documento</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedInstructor->persona->tipo_documento->nombre ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Número Documento</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedInstructor->persona->numero_documento }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Nombre Completo</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedInstructor->persona->nombre_completo }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Email</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedInstructor->persona->email ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Teléfono</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedInstructor->persona->telefono ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Información Laboral -->
                    <div class="modal-section">
                        <h6 class="section-title">Información Laboral</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Regional</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedInstructor->regional->nombre ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Centro de Formación</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedInstructor->centroFormacion->nombre ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Tipo de Vinculación</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $selectedInstructor->tipoVinculacion->parametro->name ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Experiencia</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedInstructor->anos_experiencia ?? 0 }} años / {{ $selectedInstructor->experiencia_instructor_meses ?? 0 }} meses
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Especialidades -->
                    <div class="modal-section">
                        <h6 class="section-title">Especialidades</h6>
                        @php
                            $especialidades = $this->obtenerEspecialidadesFormateadas($selectedInstructor);
                        @endphp
                        
                        @if($especialidades['principal'] || count($especialidades['secundarias']) > 0)
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                @if($especialidades['principal'])
                                    <span class="especialidad-badge principal">
                                        <i class="fas fa-star"></i> {{ $especialidades['principal'] }}
                                    </span>
                                @endif
                                
                                @foreach($especialidades['secundarias'] as $especialidad)
                                    <span class="especialidad-badge secundaria">
                                        {{ $especialidad }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p style="color: #6b7280; font-style: italic;">Sin especialidades registradas</p>
                        @endif
                    </div>
                    
                    <!-- Sección: Formación Académica -->
                    <div class="modal-section">
                        <h6 class="section-title">Formación Académica</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Nivel Académico</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedInstructor->nivelAcademico->parametro->name ?? 'N/A' }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Formación Pedagógica</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedInstructor->formacion_pedagogia ?: 'No especificada' }}
                                </div>
                            </div>
                        </div>
                        
                        @if($selectedInstructor->titulos_obtenidos && count($selectedInstructor->titulos_obtenidos) > 0)
                            <div style="margin-top: 1rem;">
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 8px;">Títulos Obtenidos</div>
                                <ul style="margin: 0; padding-left: 1.5rem;">
                                    @foreach($selectedInstructor->titulos_obtenidos as $titulo)
                                        <li style="font-size: 14px; color: #1f2937; margin-bottom: 4px;">{{ $titulo }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        @if($selectedInstructor->instituciones_educativas && count($selectedInstructor->instituciones_educativas) > 0)
                            <div style="margin-top: 1rem;">
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 8px;">Instituciones Educativas</div>
                                <ul style="margin: 0; padding-left: 1.5rem;">
                                    @foreach($selectedInstructor->instituciones_educativas as $institucion)
                                        <li style="font-size: 14px; color: #1f2937; margin-bottom: 4px;">{{ $institucion }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Sección: Competencias y Habilidades -->
                    <div class="modal-section">
                        <h6 class="section-title">Competencias y Habilidades</h6>
                        
                        @if($selectedInstructor->areas_experticia && count($selectedInstructor->areas_experticia) > 0)
                            <div style="margin-bottom: 1rem;">
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 8px;">Áreas de Experticia</div>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    @foreach($selectedInstructor->areas_experticia as $area)
                                        <span style="background: rgba(59, 130, 246, 0.1); color: var(--primary); padding: 4px 8px; border-radius: 12px; font-size: 12px;">
                                            {{ $area }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        @if($selectedInstructor->competencias_tic && count($selectedInstructor->competencias_tic) > 0)
                            <div style="margin-bottom: 1rem;">
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 8px;">Competencias TIC</div>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    @foreach($selectedInstructor->competencias_tic as $competencia)
                                        <span style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 4px 8px; border-radius: 12px; font-size: 12px;">
                                            {{ $competencia }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        @if($selectedInstructor->idiomas && count($selectedInstructor->idiomas) > 0)
                            <div style="margin-bottom: 1rem;">
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 8px;">Idiomas</div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px;">
                                    @foreach($selectedInstructor->idiomas as $idioma)
                                        <div style="background: var(--bg-light); padding: 8px; border-radius: 6px;">
                                            <div style="font-weight: 500; font-size: 14px;">{{ $idioma['idioma'] }}</div>
                                            <div style="font-size: 12px; color: #6b7280;">{{ $idioma['nivel'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        @if($selectedInstructor->modalidades && count($selectedInstructor->modalidades) > 0)
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 8px;">Modalidades (Habilidades Pedagógicas)</div>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    @foreach($selectedInstructor->modalidades as $modalidad)
                                        @if(isset($modalidad->parametro->name))
                                            <span style="background: rgba(245, 158, 11, 0.1); color: var(--warning); padding: 4px 8px; border-radius: 12px; font-size: 12px;">
                                                {{ $modalidad->parametro->name }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Sección: Información Adicional -->
                    <div class="modal-section">
                        <h6 class="section-title">Información Adicional</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Fecha Ingreso SENA</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedInstructor->fecha_ingreso_sena ? $selectedInstructor->fecha_ingreso_sena->format('d/m/Y') : 'No especificada' }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Experiencia Laboral</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedInstructor->experiencia_laboral ?: 'No especificada' }}
                                </div>
                            </div>
                        </div>
                        
                        @if($selectedInstructor->certificaciones_tecnicas && count($selectedInstructor->certificaciones_tecnicas) > 0)
                            <div style="margin-top: 1rem;">
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 8px;">Certificaciones Técnicas</div>
                                <ul style="margin: 0; padding-left: 1.5rem;">
                                    @foreach($selectedInstructor->certificaciones_tecnicas as $certificacion)
                                        <li style="font-size: 14px; color: #1f2937; margin-bottom: 4px;">{{ $certificacion }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        @if($selectedInstructor->cursos_complementarios && count($selectedInstructor->cursos_complementarios) > 0)
                            <div style="margin-top: 1rem;">
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 8px;">Cursos Complementarios</div>
                                <ul style="margin: 0; padding-left: 1.5rem;">
                                    @foreach($selectedInstructor->cursos_complementarios as $curso)
                                        <li style="font-size: 14px; color: #1f2937; margin-bottom: 4px;">{{ $curso }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Sección: Información Administrativa -->
                    <div class="modal-section">
                        <h6 class="section-title">Información Administrativa</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Número de Contrato</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedInstructor->numero_contrato ?: 'No especificado' }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Fecha Inicio Contrato</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedInstructor->fecha_inicio_contrato ? $selectedInstructor->fecha_inicio_contrato->format('d/m/Y') : 'No especificada' }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Fecha Fin Contrato</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedInstructor->fecha_fin_contrato ? $selectedInstructor->fecha_fin_contrato->format('d/m/Y') : 'No especificada' }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Supervisor de Contrato</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedInstructor->supervisor_contrato ?: 'No especificado' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección: Estado del Instructor -->
                    <div class="modal-section">
                        <h6 class="section-title">Estado del Instructor</h6>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="badge-status {{ $selectedInstructor->status ? 'badge-active' : 'badge-inactive' }}" style="padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; text-transform: uppercase;">
                                <i class="fas fa-{{ $selectedInstructor->status ? 'check' : 'times' }}" style="margin-right: 4px;"></i>
                                {{ $selectedInstructor->status ? 'Activo' : 'Inactivo' }}
                            </span>
                            <span style="font-size: 13px; color: #6b7280; font-style: italic;">
                                Este instructor {{ $selectedInstructor->status ? 'puede' : 'no puede' }} ser asignado a nuevas fichas
                            </span>
                        </div>
                    </div>
                    
                    <!-- Sección: Acciones -->
                    <div class="modal-section">
                        <h6 class="section-title">Acciones</h6>
                        <div style="display: flex; gap: 12px;">
                            @can('EDITAR INSTRUCTOR')
                                <button class="btn-modal btn-primary" wire:click="openEditModal({{ $selectedInstructor->id }})">
                                    <i class="fas fa-edit"></i>
                                    Editar Instructor
                                </button>
                            @endcan
                            <button class="btn-modal {{ $selectedInstructor->status ? 'btn-danger' : 'btn-success' }}" 
                                    wire:click="toggleStatus({{ $selectedInstructor->id }})" 
                                    wire:loading.attr="disabled">
                                <i wire:loading.remove wire:target="toggleStatus" class="fas fa-sync-alt"></i>
                                <span wire:loading.remove wire:target="toggleStatus">
                                    {{ $selectedInstructor->status ? 'Desactivar Instructor' : 'Activar Instructor' }}
                                </span>
                                <i wire:loading wire:target="toggleStatus" class="fas fa-spinner fa-spin"></i>
                                <span wire:loading wire:target="toggleStatus">Procesando...</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Sección: Auditoría -->
                    <div class="modal-section">
                        <h6 class="section-title">Auditoría</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                            <div>
                                <div style="font-size: 12px; color: #9ca3af; text-transform: uppercase; margin-bottom: 4px;">Creado por</div>
                                <div style="font-size: 13px; color: #374151; font-weight: 500;">{{ $selectedInstructor->userCreated->name ?? 'Sistema' }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $selectedInstructor->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #9ca3af; text-transform: uppercase; margin-bottom: 4px;">Última edición</div>
                                <div style="font-size: 13px; color: #374151; font-weight: 500;">{{ $selectedInstructor->userEdited->name ?? 'Sin edición' }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $selectedInstructor->updated_at->format('d/m/Y H:i') }}</div>
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
        </div>
    @endif

    <!-- Modal Eliminar Instructor -->
    @if ($showDeleteModal && $selectedInstructor)
        <div class="modal-overlay" wire:click="closeDeleteModal">
            <div class="modal-container" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Instructor</h5>
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
                        <h6 class="section-title">Información del Instructor</h6>
                        <div style="display: grid; gap: 8px;">
                            <div><strong>Nombre:</strong> {{ $selectedInstructor->persona->nombre_completo }}</div>
                            <div><strong>Documento:</strong> {{ $selectedInstructor->persona->numero_documento }}</div>
                            <div><strong>Regional:</strong> {{ $selectedInstructor->regional->nombre ?? 'N/A' }}</div>
                            <div><strong>Email:</strong> {{ $selectedInstructor->persona->email ?? 'N/A' }}</div>
                        </div>
                    </div>
                    
                    <!-- Mensaje de confirmación -->
                    <div class="modal-section">
                        <p style="margin: 0; color: #6b7280; font-size: 14px;">
                            ¿Está seguro de que desea eliminar este instructor?
                        </p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="modal-footer">
                    <button class="btn-erp btn-secondary" wire:click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button class="btn-erp btn-danger" wire:click="deleteInstructor({{ $selectedInstructor->id }})" 
                            wire:loading.attr="disabled">
                        <i wire:loading.remove wire:target="deleteInstructor" class="fas fa-trash"></i>
                        <span wire:loading.remove wire:target="deleteInstructor">Eliminar</span>
                        <i wire:loading wire:target="deleteInstructor" class="fas fa-spinner fa-spin"></i>
                        <span wire:loading wire:target="deleteInstructor">Eliminando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Gestionar Especialidades -->
    @if ($showEspecialidadesModal && $selectedInstructor)
        <div class="modal-overlay" wire:click="closeEspecialidadesModal">
            <div class="modal-container modal-xl" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Gestionar Especialidades
                    </h5>
                    <button class="modal-close" wire:click="closeEspecialidadesModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <!-- Información del Instructor -->
                    <div class="modal-section">
                        <h6 class="section-title">Información del Instructor</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Nombre</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedInstructor->persona->primer_nombre }} {{ $selectedInstructor->persona->primer_apellido }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Documento</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedInstructor->persona->numero_documento }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Regional</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedInstructor->regional->nombre ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <div class="modal-section">
                        <h6 class="section-title">Estadísticas de Especialidades</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
                            <div style="text-align: center; padding: 1rem; background: var(--bg-light); border-radius: 8px;">
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                                    {{ $especialidadesAsignadas['principal'] ? 1 : 0 }}
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Principal</div>
                            </div>
                            <div style="text-align: center; padding: 1rem; background: var(--bg-light); border-radius: 8px;">
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--info);">
                                    {{ count($especialidadesAsignadas['secundarias']) }}
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Secundarias</div>
                            </div>
                            <div style="text-align: center; padding: 1rem; background: var(--bg-light); border-radius: 8px;">
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning);">
                                    {{ $redesConocimientoDisponibles->count() }}
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Disponibles</div>
                            </div>
                        </div>
                    </div>

                    <!-- Especialidades Asignadas -->
                    <div class="modal-section">
                        <h6 class="section-title">Especialidades Asignadas</h6>
                        @if($especialidadesAsignadas['principal'] || count($especialidadesAsignadas['secundarias']) > 0)
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
                                @if($especialidadesAsignadas['principal'])
                                    <div style="padding: 1rem; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-light);">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                            <span class="badge-modern badge-primary">
                                                <i class="fas fa-star me-1"></i>Principal
                                            </span>
                                        </div>
                                        <div style="font-weight: 500;">{{ $especialidadesAsignadas['principal'] }}</div>
                                    </div>
                                @endif
                                
                                @foreach($especialidadesAsignadas['secundarias'] as $especialidad)
                                    <div style="padding: 1rem; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-light);">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                            <span class="badge-modern badge-info">
                                                <i class="fas fa-circle me-1"></i>Secundaria
                                            </span>
                                        </div>
                                        <div style="font-weight: 500;">{{ $especialidad }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="text-align: center; padding: 2rem; color: #6b7280;">
                                <i class="fas fa-graduation-cap fa-2x mb-2"></i>
                                <p>Este instructor no tiene especialidades asignadas</p>
                            </div>
                        @endif
                    </div>

                    <!-- Especialidades Disponibles -->
                    <div class="modal-section">
                        <h6 class="section-title">Especialidades Disponibles</h6>
                        @if($redesConocimientoDisponibles->count() > 0)
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
                                @foreach($redesConocimientoDisponibles as $redConocimiento)
                                    <div style="padding: 1rem; border: 1px solid var(--border); border-radius: 8px;">
                                        <div style="font-weight: 500; margin-bottom: 0.5rem;">{{ $redConocimiento->nombre }}</div>
                                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 1rem;">
                                            {{ $redConocimiento->descripcion ?? 'Sin descripción' }}
                                        </div>
                                        <div style="display: flex; gap: 8px;">
                                            <button class="btn-modal btn-primary" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                                <i class="fas fa-star me-1"></i>Principal
                                            </button>
                                            <button class="btn-modal btn-info" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                                <i class="fas fa-plus me-1"></i>Secundaria
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="text-align: center; padding: 2rem; color: #6b7280;">
                                <i class="fas fa-graduation-cap fa-2x mb-2"></i>
                                <p>No hay especialidades disponibles para asignar</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button class="btn-modal btn-secondary" wire:click="closeEspecialidadesModal">
                        <i class="fas fa-times"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Fichas Asignadas -->
    @if ($showFichasModal && $selectedInstructor)
        <div class="modal-overlay" wire:click="closeFichasModal">
            <div class="modal-container modal-xl" wire:click.stop>
                
                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Fichas Asignadas
                    </h5>
                    <button class="modal-close" wire:click="closeFichasModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <!-- Información del Instructor -->
                    <div class="modal-section">
                        <h6 class="section-title">Información del Instructor</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Nombre</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedInstructor->persona->primer_nombre }} {{ $selectedInstructor->persona->primer_apellido }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Documento</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedInstructor->persona->numero_documento }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Regional</div>
                                <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                    {{ $selectedInstructor->regional->nombre ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <div class="modal-section">
                        <h6 class="section-title">Estadísticas</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
                            <div style="text-align: center; padding: 1rem; background: var(--bg-light); border-radius: 8px;">
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">{{ $fichasAsignadas->count() }}</div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Total Fichas</div>
                            </div>
                            <div style="text-align: center; padding: 1rem; background: var(--bg-light); border-radius: 8px;">
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);">{{ $fichasAsignadas->where('ficha.status', true)->count() }}</div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Activas</div>
                            </div>
                            <div style="text-align: center; padding: 1rem; background: var(--bg-light); border-radius: 8px;">
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning);">{{ $fichasAsignadas->sum('total_horas_instructor') ?? 0 }}</div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Horas Asignadas</div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Fichas -->
                    <div class="modal-section">
                        <h6 class="section-title">Lista de Fichas</h6>
                        @if($fichasAsignadas->count() > 0)
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                                    <thead>
                                        <tr style="background: var(--bg-light);">
                                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; border-bottom: 1px solid var(--border);">Ficha</th>
                                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; border-bottom: 1px solid var(--border);">Programa</th>
                                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; border-bottom: 1px solid var(--border);">Fecha Inicio</th>
                                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; border-bottom: 1px solid var(--border);">Fecha Fin</th>
                                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; border-bottom: 1px solid var(--border);">Horas</th>
                                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; border-bottom: 1px solid var(--border);">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($fichasAsignadas as $ficha)
                                            <tr style="border-bottom: 1px solid var(--border);">
                                                <td style="padding: 0.75rem;">
                                                    <span class="badge-modern badge-primary">{{ $ficha->ficha->ficha ?? 'N/A' }}</span>
                                                </td>
                                                <td style="padding: 0.75rem;">{{ $ficha->ficha->programaFormacion->nombre ?? 'N/A' }}</td>
                                                <td style="padding: 0.75rem;">
                                                    {{ $ficha->ficha->fecha_inicio ? $ficha->ficha->fecha_inicio->format('d/m/Y') : 'N/A' }}
                                                </td>
                                                <td style="padding: 0.75rem;">
                                                    {{ $ficha->ficha->fecha_fin ? $ficha->ficha->fecha_fin->format('d/m/Y') : 'N/A' }}
                                                </td>
                                                <td style="padding: 0.75rem;">
                                                    <span class="badge-modern badge-info">{{ $ficha->total_horas_instructor ?? 0 }}h</span>
                                                </td>
                                                <td style="padding: 0.75rem;">
                                                    <span class="badge-modern {{ $ficha->ficha->status ? 'badge-success' : 'badge-danger' }}">
                                                        {{ $ficha->ficha->status ? 'Activa' : 'Inactiva' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div style="text-align: center; padding: 2rem; color: #6b7280;">
                                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                <p>Este instructor no tiene fichas asignadas</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button class="btn-modal btn-secondary" wire:click="closeFichasModal">
                        <i class="fas fa-times"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
