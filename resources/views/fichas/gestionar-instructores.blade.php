@extends('adminlte::page')

@section('plugins.Select2', true)

@section('title', 'Gestionar Instructores - Ficha ' . $ficha->ficha)

@section('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('css')
    @vite(['resources/css/parametros.css'])
    {{-- Select2 cargado por AdminLTE nativo --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <style>
        /* Estilos para Select2 en selects dinámicos */
        .instructor-select {
            width: 100% !important;
            min-width: 200px;
        }
        
        .instructor-row .select2-container {
            width: 100% !important;
            margin-bottom: 0;
        }
        
        .instructor-row .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        
        .instructor-row .select2-container--bootstrap-5 .select2-selection__rendered {
            padding-left: 12px;
            padding-right: 20px;
            line-height: 36px;
        }
        
        .instructor-row .select2-container--bootstrap-5 .select2-selection__arrow {
            height: 36px;
            right: 8px;
        }
        
        /* Estilos para badges de estado */
        .badge {
            font-size: 0.75rem;
        }
        
        /* Estilos para alertas expandibles */
        .alert {
            border-radius: 0.375rem;
        }
        
        /* Estilos para alertas de error mejorados */
        .alert-danger {
            border-left: 4px solid #dc3545;
            animation: slideDown 0.4s ease-out;
        }
        
        .alert-success {
            border-left: 4px solid #28a745;
            animation: slideDown 0.4s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-heading {
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .alert ul {
            padding-left: 1.5rem;
        }
        
        .alert ul li {
            margin-bottom: 0.5rem;
        }
        
        /* Estilos para tabla de instructores */
        .table-responsive {
            border-radius: 0.375rem;
        }
        
        .table tbody tr.table-warning {
            background-color: rgba(255, 193, 7, 0.1);
        }
        
        .table tbody tr.table-info {
            background-color: rgba(23, 162, 184, 0.1);
        }
        
        .table tbody tr.table-light {
            background-color: rgba(248, 249, 250, 0.8);
        }
        
        /* Estilos para estadísticas */
        .border-left-primary {
            border-left: 0.25rem solid #007bff !important;
        }
        
        .border-left-success {
            border-left: 0.25rem solid #28a745 !important;
        }
        
        .border-left-warning {
            border-left: 0.25rem solid #ffc107 !important;
        }
        
        .border-left-info {
            border-left: 0.25rem solid #17a2b8 !important;
        }
        
        .border-left-danger {
            border-left: 0.25rem solid #dc3545 !important;
        }
        
        /* Estilos para hover effects */
        .hover-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .hover-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        }
        
        /* Estilos para checkboxes azules personalizados */
        .custom-checkbox-blue .form-check-input {
            width: 1.25rem;
            height: 1.25rem;
            margin-top: 0.125rem;
            cursor: pointer;
            border: 2px solid #6c757d;
            border-radius: 0.25rem;
            transition: all 0.2s ease;
        }
        
        .custom-checkbox-blue .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        .custom-checkbox-blue .form-check-input:focus {
            border-color: #007bff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .custom-checkbox-blue .form-check-input:hover:not(:checked) {
            border-color: #007bff;
            background-color: rgba(0, 123, 255, 0.1);
        }
        
        .custom-checkbox-blue .form-check-label {
            cursor: pointer;
            user-select: none;
            margin-left: 0.5rem;
            color: #212529;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .custom-checkbox-blue .form-check-input:checked ~ .form-check-label {
            color: #212529;
            font-weight: 600;
        }
        
        .custom-checkbox-blue .form-check-input:hover ~ .form-check-label {
            color: #212529;
        }
    </style>
@endsection

@section('content_header')
    <x-page-header 
        icon="fa-chalkboard-teacher" 
        title="Gestionar Instructores"
        subtitle="Ficha {{ $ficha->ficha }}"
        :breadcrumb="[['label' => 'Ficha {{ $ficha->ficha }}', 'url' => route('fichaCaracterizacion.show', $ficha->id) , 'icon' => 'fa-eye'], ['label' => 'Gestionar Instructores', 'icon' => 'fa-chalkboard-teacher', 'active' => true]]"
    />
@endsection

@section('content')
    <section class="content mt-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <a class="btn btn-outline-secondary btn-sm mb-3" href="{{ route('fichaCaracterizacion.show', $ficha->id) }}">
                        <i class="fas fa-arrow-left mr-1"></i> Volver a la Ficha
                    </a>

                    <!-- Información de la Ficha -->
                    <div class="card detail-card no-hover mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title m-0 font-weight-bold text-primary">
                                <i class="fas fa-info-circle mr-2"></i>Información de la Ficha
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="info-item">
                                        <strong>Programa:</strong><br>
                                        <span class="text-muted">{{ $ficha->programaFormacion->nombre ?? 'No asignado' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-item">
                                        <strong>Fecha Inicio:</strong><br>
                                        <span class="text-muted">{{ $ficha->fecha_inicio ? $ficha->fecha_inicio->format('d/m/Y') : 'No definida' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-item">
                                        <strong>Fecha Fin:</strong><br>
                                        <span class="text-muted">{{ $ficha->fecha_fin ? $ficha->fecha_fin->format('d/m/Y') : 'No definida' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-item">
                                        <strong>Total Horas:</strong><br>
                                        <span class="text-muted">{{ $ficha->total_horas ?? 'No definido' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de Asignación de Instructores -->
                    <div class="card border-0 shadow-lg mb-4">
                        <div class="card-header bg-white border-0 py-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle p-2 me-3">
                                    <i class="fas fa-users text-white"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 text-dark">Asignar Instructores</h4>
                                    <small class="text-muted">Agregue instructores adicionales a esta ficha</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            {{-- Mostrar errores de validación --}}
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <h5 class="alert-heading"><i class="fas fa-exclamation-triangle mr-2"></i>Error en la asignación</h5>
                                    <hr>
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Mostrar mensajes de éxito --}}
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                                </div>
                            @endif

                            <form action="{{ route('fichaCaracterizacion.asignarInstructores', $ficha->id) }}" method="POST" id="formAsignarInstructores">
                                @csrf
                                
                                {{-- Campo oculto para instructor principal --}}
                                <input type="hidden" 
                                       name="instructor_principal_id" 
                                       id="instructor_principal_id" 
                                       value="{{ old('instructor_principal_id', $ficha->instructor_id) }}">

                                <!-- Información de fechas permitidas -->
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Rango de fechas permitidas:</strong>
                                    @if($ficha->fecha_inicio && $ficha->fecha_fin)
                                        Desde {{ $ficha->fecha_inicio->format('d/m/Y') }} hasta {{ $ficha->fecha_fin->format('d/m/Y') }}
                                    @else
                                        <span class="text-warning">Las fechas de la ficha no están definidas</span>
                                    @endif
                                </div>

                                <!-- Información de días de formación -->
                                @if(isset($diasFormacionFicha) && $diasFormacionFicha->count() > 0)
                                    <div class="alert alert-success">
                                        <i class="fas fa-calendar-check"></i>
                                        <strong>Días de formación disponibles:</strong>
                                        {{ $diasFormacionFicha->map(function($diaFormacion) { return $diaFormacion->dia->name ?? ''; })->filter()->implode(', ') }}
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Advertencia:</strong> Esta ficha no tiene días de formación asignados. 
                                        <a href="{{ route('fichaCaracterizacion.gestionarDiasFormacion', $ficha->id) }}" class="alert-link">
                                            Asignar días de formación
                                        </a>
                                    </div>
                                @endif

                                <!-- Lista de Instructores -->
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">
                                        <i class="fas fa-users mr-1"></i>
                                        Instructores Asignados <span class="text-danger">*</span>
                                    </label>
                                    <div id="instructores-container">   
                                        <!-- Los instructores se agregarán dinámicamente aquí -->
                                    </div>
                                    <button type="button" class="btn btn-primary btn-lg mt-4" onclick="agregarInstructor()">
                                        <i class="fas fa-plus me-2"></i> Agregar Instructor
                                    </button>
                                </div>

                                <div class="border-top pt-4 mt-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('fichaCaracterizacion.show', $ficha->id) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-2"></i> Volver
                                        </a>
                                        <button type="submit" class="btn btn-success btn-lg px-4">
                                            <i class="fas fa-check me-2"></i> Asignar Instructores
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Instructores Asignados -->
                    <div class="card border-0 shadow-lg mb-4">
                        <div class="card-header bg-white border-0 py-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-success rounded-circle p-2 me-3">
                                    <i class="fas fa-user-check text-white"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 text-dark">Instructores Asignados</h4>
                                    <small class="text-muted">Instructores ya asignados a esta ficha</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            @if($instructoresAsignados->count() > 0)
                                <div class="row g-3">
                                    @foreach($instructoresAsignados as $asignacion)
                                        <div class="col-md-6">
                                            <div class="bg-light border rounded p-3">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1 text-dark">
                                                            {{ $asignacion->instructor->persona->primer_nombre }} 
                                                            {{ $asignacion->instructor->persona->primer_apellido }}
                                                            @if($ficha->instructor_id == $asignacion->instructor_id)
                                                                <span class="badge bg-primary ms-2">Principal</span>
                                                            @else
                                                                <span class="badge bg-secondary ms-2">Auxiliar</span>
                                                            @endif
                                                            @if($asignacion->instructorFichaDias && $asignacion->instructorFichaDias->count() > 0)
                                                                <span class="badge bg-success ms-2" title="Tiene días asignados">
                                                                    <i class="fas fa-check-circle"></i> Días configurados
                                                                </span>
                                                            @else
                                                                <span class="badge bg-warning text-dark ms-2" title="Sin días asignados">
                                                                    <i class="fas fa-exclamation-triangle"></i> Sin días
                                                                </span>
                                                            @endif
                                                        </h6>
                                                        <p class="text-muted mb-1 small">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            {{ $asignacion->fecha_inicio->format('d/m/Y') }} - 
                                                            {{ $asignacion->fecha_fin->format('d/m/Y') }}
                                                        </p>
                                                        <p class="text-muted mb-0 small">
                                                            <i class="fas fa-clock me-1"></i>
                                                            {{ $asignacion->total_horas_instructor }} horas
                                                        </p>
                                                        @if($asignacion->instructorFichaDias && $asignacion->instructorFichaDias->count() > 0)
                                                            @php
                                                                $diasAsignados = $asignacion->instructorFichaDias
                                                                    ->filter(function($dia) { return $dia->dia && $dia->dia->name; })
                                                                    ->map(function($dia) { return $dia->dia->name; })
                                                                    ->implode(', ');
                                                            @endphp
                                                            @if($diasAsignados)
                                                                <p class="text-muted mb-0 small mt-1">
                                                                    <i class="fas fa-calendar-week me-1"></i>
                                                                    <strong>Días:</strong> {{ $diasAsignados }}
                                                                </p>
                                                            @endif
                                                        @endif
                                                    </div>
                                                    <div class="btn-group-vertical btn-group-sm" role="group">
                                                        <!-- Botón para editar asignación completa -->
                                                        <button type="button"
                                                           class="btn btn-sm btn-primary mb-1 btn-editar-instructor" 
                                                           data-instructor-ficha-id="{{ $asignacion->id }}"
                                                           data-instructor-nombre="{{ $asignacion->instructor->persona->primer_nombre }} {{ $asignacion->instructor->persona->primer_apellido }}"
                                                           data-fecha-inicio="{{ $asignacion->fecha_inicio ? $asignacion->fecha_inicio->format('Y-m-d') : '' }}"
                                                           data-fecha-fin="{{ $asignacion->fecha_fin ? $asignacion->fecha_fin->format('Y-m-d') : '' }}"
                                                           data-competencia-id="{{ $asignacion->competencia_id }}"
                                                           data-resultados-ids="{{ $asignacion->resultadosAprendizaje->pluck('id')->implode(',') }}"
                                                           data-toggle="tooltip" 
                                                           data-placement="top"
                                                           title="Editar asignación completa (fechas, días, competencias y resultados)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        
                                                        <!-- Botón para desasignar instructor -->
                                                        <form action="{{ route('fichaCaracterizacion.desasignarInstructor', [$ficha->id, $asignacion->instructor_id]) }}" 
                                                              method="POST" style="display: inline;" 
                                                              class="form-desasignar-instructor">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-desasignar-instructor" 
                                                                    data-toggle="tooltip"
                                                                    data-placement="top"
                                                                    data-instructor-nombre="{{ $asignacion->instructor->persona->primer_nombre }} {{ $asignacion->instructor->persona->primer_apellido }}"
                                                                    title="Desasignar instructor">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-user-slash fa-3x mb-3"></i>
                                    <p>No hay instructores adicionales asignados a esta ficha.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Modal Unificado para Editar Instructor Asignado -->
    <div class="modal fade" id="modalEditarInstructor" tabindex="-1" role="dialog" aria-labelledby="modalEditarInstructorLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalEditarInstructorLabel">
                        <i class="fas fa-user-edit mr-2"></i>Editar Asignación de Instructor
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Información del instructor -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle mr-2"></i>Información</h6>
                        <p class="mb-1"><strong>Instructor:</strong> <span id="modal-instructor-nombre"></span></p>
                        <p class="mb-1"><strong>Ficha:</strong> {{ $ficha->ficha }}</p>
                        <p class="mb-0"><strong>Programa:</strong> {{ $ficha->programaFormacion->nombre ?? 'N/A' }}</p>
                    </div>

                    <!-- Formulario unificado -->
                    <form id="form-editar-instructor-modal">
                        <input type="hidden" id="modal-instructor-ficha-id">
                        
                        <!-- Sección de Fechas -->
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-calendar-alt mr-2"></i>Fechas de Asignación
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label font-weight-bold">
                                            <i class="fas fa-calendar-alt mr-1"></i> Fecha Inicio <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" id="modal-fecha-inicio" class="form-control" required>
                                        <small class="text-muted">Rango permitido: {{ $ficha->fecha_inicio ? $ficha->fecha_inicio->format('d/m/Y') : 'N/A' }} - {{ $ficha->fecha_fin ? $ficha->fecha_fin->format('d/m/Y') : 'N/A' }}</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label font-weight-bold">
                                            <i class="fas fa-calendar-check mr-1"></i> Fecha Fin <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" id="modal-fecha-fin" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de Competencias y Resultados -->
                        <div class="card mb-3">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-tasks mr-2"></i>Competencias y Resultados de Aprendizaje
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label font-weight-bold">
                                            <i class="fas fa-tasks mr-1"></i> Competencia
                                        </label>
                                        <select id="modal-competencias-select" 
                                                class="form-control select2" 
                                                data-placeholder="Seleccionar competencia...">
                                            <option value="">Seleccionar competencia...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label font-weight-bold">
                                            <i class="fas fa-graduation-cap mr-1"></i> Resultados de Aprendizaje
                                        </label>
                                        <select id="modal-resultados-select" 
                                                class="form-control select2" 
                                                data-placeholder="Seleccionar resultados de aprendizaje..."
                                                multiple>
                                        </select>
                                        <small class="text-muted">Seleccione uno o varios resultados de aprendizaje de la competencia</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de Días de Formación -->
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-calendar-week mr-2"></i>Días de Formación
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th width="10%" class="text-center">
                                                    <input type="checkbox" id="select-all-modal" title="Seleccionar todos">
                                                </th>
                                                <th width="25%">Día de la Semana</th>
                                                <th width="30%">Hora Inicio</th>
                                                <th width="30%">Hora Fin</th>
                                                <th width="5%" class="text-center"><i class="fas fa-info-circle"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody id="dias-tbody">
                                            @if(isset($diasFormacionFicha) && $diasFormacionFicha->count() > 0)
                                                @foreach($diasFormacionFicha as $diaFormacion)
                                                    @php
                                                        $dia = $diaFormacion->dia ?? null;
                                                    @endphp
                                                    @if($dia)
                                                    <tr class="dia-row-modal" data-dia-id="{{ $dia->id }}">
                                                        <td class="text-center align-middle">
                                                            <input type="checkbox" class="dia-checkbox-modal" value="{{ $dia->id }}" name="dias_selected[]">
                                                        </td>
                                                        <td class="align-middle">
                                                            <strong><i class="far fa-calendar mr-1"></i>{{ $dia->name }}</strong>
                                                        </td>
                                                        <td>
                                                            <input type="time" class="form-control hora-inicio-modal" name="hora_inicio_{{ $dia->id }}" data-dia="{{ $dia->id }}" disabled>
                                                        </td>
                                                        <td>
                                                            <input type="time" class="form-control hora-fin-modal" name="hora_fin_{{ $dia->id }}" data-dia="{{ $dia->id }}" disabled>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <i class="fas fa-check-circle text-success dia-status-modal" style="display:none;"></i>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">
                                                        No hay días de formación configurados para esta ficha. 
                                                        <a href="{{ route('fichaCaracterizacion.gestionarDiasFormacion', $ficha->id) }}" class="alert-link">
                                                            Configurar días de formación
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Panel de Información y Consejos -->
                        <div class="card border-warning" id="info-panel-modal">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle mr-2"></i>Información y Consejos de Asignación
                                </h6>
                            </div>
                            <div class="card-body" id="info-content-modal">
                                <div class="text-muted text-center">
                                    <i class="fas fa-arrow-up mr-2"></i>
                                    Complete los campos arriba para ver información y consejos
                                </div>
                            </div>
                        </div>

                        <!-- Preview de fechas -->
                        <div id="preview-fechas-modal" class="card card-success mt-3" style="display: none;">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-check mr-2"></i>Fechas Efectivas de Formación
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="fechas-container-modal"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" id="btn-preview-modal">
                        <i class="fas fa-eye mr-1"></i>Vista Previa
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-success" id="btn-guardar-instructor-modal">
                        <i class="fas fa-save mr-1"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer')
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Pasar datos al contexto de JavaScript
        window.fichaId = {{ $ficha->id }};
        
        // Fechas de la ficha para restricciones
        window.fichaFechaInicio = @json($ficha->fecha_inicio ? $ficha->fecha_inicio->format('Y-m-d') : null);
        window.fichaFechaFin = @json($ficha->fecha_fin ? $ficha->fecha_fin->format('Y-m-d') : null);
        
        // Días de formación de la ficha (solo estos estarán disponibles)
        @php
            $diasFormacionFichaIds = isset($diasFormacionFicha) && $diasFormacionFicha->isNotEmpty() 
                ? $diasFormacionFicha->pluck('dia_id')->toArray() 
                : [];
        @endphp
        window.diasFormacionFichaIds = @json($diasFormacionFichaIds);
        
        // Jornada de la ficha para validaciones
        window.fichaJornadaNombre = @json($ficha->jornadaFormacion->jornada ?? null);
        window.fichaJornadaId = @json($ficha->jornada_id ?? null);
        
        // Crear array de días disponibles para el formulario de asignación
        window.diasSemanaDisponibles = [];
        @if(isset($diasFormacionFicha) && $diasFormacionFicha->count() > 0)
            @foreach($diasFormacionFicha as $diaFormacion)
                @php
                    $dia = $diaFormacion->dia ?? null;
                @endphp
                @if($dia)
                window.diasSemanaDisponibles.push({
                    id: {{ $dia->id }},
                    nombre: '{{ $dia->name }}'
                });
                @endif
            @endforeach
        @endif
        
        // Crear objeto de días de formación de la ficha con horas por defecto
        window.diasSemanaData = {};
        @if(isset($diasFormacionFicha) && $diasFormacionFicha->count() > 0)
            @foreach($diasFormacionFicha as $diaFormacion)
                @php
                    $dia = $diaFormacion->dia ?? null;
                    $horaInicio = $diaFormacion->hora_inicio 
                        ? \Carbon\Carbon::parse($diaFormacion->hora_inicio)->format('H:i') 
                        : ($ficha->jornadaFormacion->hora_inicio ?? '08:00');
                    $horaFin = $diaFormacion->hora_fin 
                        ? \Carbon\Carbon::parse($diaFormacion->hora_fin)->format('H:i') 
                        : ($ficha->jornadaFormacion->hora_fin ?? '12:00');
                @endphp
                @if($dia)
                window.diasSemanaData[{{ $dia->id }}] = {
                    id: {{ $dia->id }},
                    name: '{{ $dia->name }}',
                    hora_inicio: '{{ $horaInicio }}',
                    hora_fin: '{{ $horaFin }}'
                };
                @endif
            @endforeach
        @endif
        
        // Datos antiguos del formulario (para restaurar después de errores de validación)
        window.oldInstructores = @json(old('instructores', []));
    </script>
    @vite(['resources/js/pages/gestion-especializada.js'])
    <script>
        const fichaId = {{ $ficha->id }};
        let instructorFichaIdActual = null;

        $(document).ready(function() {
            // Inicializar tooltips de Bootstrap
            $('[data-toggle="tooltip"]').tooltip();
            
            // Seleccionar/deseleccionar todos en modal
            $('#select-all-modal').change(function() {
                const isChecked = $(this).is(':checked');
                $('.dia-checkbox-modal').prop('checked', isChecked).trigger('change');
            });

            // Habilitar/deshabilitar campos de hora según checkbox en modal (usando delegación de eventos)
            $(document).on('change', '.dia-checkbox-modal', function() {
                const diaId = $(this).val();
                const isChecked = $(this).is(':checked');
                const $row = $(this).closest('tr');
                
                $(`input[name="hora_inicio_${diaId}"]`).prop('disabled', !isChecked);
                $(`input[name="hora_fin_${diaId}"]`).prop('disabled', !isChecked);
                
                $row.find('.dia-status-modal').toggle(isChecked);
                
                if (isChecked) {
                    $row.addClass('table-active');
                } else {
                    $row.removeClass('table-active');
                    $(`input[name="hora_inicio_${diaId}"]`).val('');
                    $(`input[name="hora_fin_${diaId}"]`).val('');
                }
                
                // Actualizar panel de información cuando cambia un checkbox
                actualizarPanelInfoModal();
            });

            // Actualizar panel cuando cambian las horas
            $(document).on('change', '.hora-inicio-modal, .hora-fin-modal', function() {
                actualizarPanelInfoModal();
            });

            // Preview de fechas en modal
            $('#btn-preview-modal').click(function() {
                const diasSeleccionados = obtenerDiasSeleccionadosModal();
                
                if (diasSeleccionados.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin días seleccionados',
                        text: 'Debe seleccionar al menos un día para ver las fechas efectivas'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Generando fechas...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: `/fichaCaracterizacion/${fichaId}/instructor/${instructorFichaIdActual}/preview-fechas`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        dias: diasSeleccionados
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            mostrarPreviewFechasModal(response.fechas_efectivas, response.total_sesiones);
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudieron generar las fechas'
                        });
                    }
                });
            });

            // Inicializar Select2 para el modal unificado
            $('#modal-competencias-select, #modal-resultados-select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Seleccionar...',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });

            // Aplicar restricciones de fechas
            if (window.fichaFechaInicio) {
                $('#modal-fecha-inicio, #modal-fecha-fin').attr('min', window.fichaFechaInicio);
            }
            if (window.fichaFechaFin) {
                $('#modal-fecha-inicio, #modal-fecha-fin').attr('max', window.fichaFechaFin);
            }

            // Validar que fecha fin sea posterior o igual a fecha inicio
            $(document).on('change', '#modal-fecha-inicio', function() {
                const fechaInicio = $(this).val();
                if (fechaInicio) {
                    $('#modal-fecha-fin').attr('min', fechaInicio);
                    if ($('#modal-fecha-fin').val() && $('#modal-fecha-fin').val() < fechaInicio) {
                        $('#modal-fecha-fin').val('');
                    }
                }
                actualizarPanelInfoModal();
            });

            $(document).on('change', '#modal-fecha-fin', function() {
                const fechaFin = $(this).val();
                if (fechaFin) {
                    $('#modal-fecha-inicio').attr('max', fechaFin);
                    if ($('#modal-fecha-inicio').val() && $('#modal-fecha-inicio').val() > fechaFin) {
                        $('#modal-fecha-inicio').val('');
                    }
                }
                actualizarPanelInfoModal();
            });

            console.log('✅ Modal unificado inicializado');
        });

        // Función para abrir el modal unificado
        function abrirModalEditarInstructor(instructorFichaId, instructorNombre, fechaInicio, fechaFin, competenciaId, resultadosIds) {
            instructorFichaIdActual = instructorFichaId;
            
            // Establecer información del instructor
            $('#modal-instructor-nombre').text(instructorNombre);
            $('#modal-instructor-ficha-id').val(instructorFichaId);
            
            // Establecer fechas
            $('#modal-fecha-inicio').val(fechaInicio || '');
            $('#modal-fecha-fin').val(fechaFin || '');
            
            // Limpiar competencias y resultados
            $('#modal-competencias-select').val(null).trigger('change');
            $('#modal-resultados-select').val(null).trigger('change');
            
            // Limpiar días
            $('.dia-checkbox-modal').prop('checked', false).trigger('change');
            $('#select-all-modal').prop('checked', false);
            
            // Cargar competencia y resultados asignados (solo los asignados al instructor)
            cargarCompetenciasModal(instructorFichaId);
            
            // Cargar días asignados
            cargarDiasAsignadosModal(instructorFichaId);
            
            // Mostrar modal
            $('#modalEditarInstructor').modal('show');
            
            // Actualizar panel después de que el modal esté completamente visible
            setTimeout(() => {
                actualizarPanelInfoModal();
            }, 500);
        }

        // Cargar días asignados
        function cargarDiasAsignadosModal(instructorFichaId) {
            $.ajax({
                url: `/fichaCaracterizacion/${fichaId}/instructor/${instructorFichaId}/obtener-dias`,
                method: 'GET',
                success: function(response) {
                    if (response.success && response.data && response.data.length > 0) {
                        response.data.forEach(function(dia) {
                            const $checkbox = $(`.dia-checkbox-modal[value="${dia.dia_id}"]`);
                            if ($checkbox.length) {
                                // Primero establecer los valores de hora antes de activar el checkbox
                                if (dia.hora_inicio) {
                                    $(`input[name="hora_inicio_${dia.dia_id}"]`).val(dia.hora_inicio);
                                }
                                if (dia.hora_fin) {
                                    $(`input[name="hora_fin_${dia.dia_id}"]`).val(dia.hora_fin);
                                }
                                
                                // Luego activar el checkbox (esto habilitará los campos)
                                $checkbox.prop('checked', true).trigger('change');
                            }
                        });
                        
                        // Actualizar panel después de cargar todos los días
                        setTimeout(() => {
                            actualizarPanelInfoModal();
                        }, 300);
                    } else {
                        // Si no hay días asignados, actualizar el panel de todas formas
                        setTimeout(() => {
                            actualizarPanelInfoModal();
                        }, 100);
                    }
                },
                error: function() {
                    console.log('No se pudieron cargar los días asignados');
                    // Actualizar panel incluso si hay error
                    setTimeout(() => {
                        actualizarPanelInfoModal();
                    }, 100);
                }
            });
        }

        // Cargar competencias disponibles
        function cargarCompetenciasModal(instructorFichaId = null) {
            // Si estamos editando, cargar solo la competencia asignada
            if (instructorFichaId) {
                $.ajax({
                    url: `/fichaCaracterizacion/${fichaId}/instructor/${instructorFichaId}/competencia-resultados`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success && response.data) {
                            const $select = $('#modal-competencias-select');
                            $select.empty();
                            $select.append('<option value="">Seleccionar competencia...</option>');
                            
                            // Solo mostrar la competencia asignada
                            if (response.data.competencia) {
                                const competencia = response.data.competencia;
                                $select.append(`<option value="${competencia.id}">${competencia.codigo} - ${competencia.nombre}</option>`);
                                
                                // Seleccionar la competencia
                                $select.val(competencia.id).trigger('change');
                                
                                // Cargar solo los resultados asignados
                                cargarResultadosAsignadosModal(response.data.resultados);
                            }
                            
                            $select.trigger('change');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar competencia y resultados:', error);
                    }
                });
            } else {
                // Si no estamos editando, cargar todas las competencias disponibles
                $.ajax({
                    url: `/asignaciones/instructores/fichas/${fichaId}/competencias`,
                    method: 'GET',
                    success: function(response) {
                        const $select = $('#modal-competencias-select');
                        $select.empty();
                        $select.append('<option value="">Seleccionar competencia...</option>');
                        
                        if (response.data && response.data.length > 0) {
                            response.data.forEach(function(competencia) {
                                $select.append(`<option value="${competencia.id}">${competencia.codigo} - ${competencia.nombre}</option>`);
                            });
                        }
                        
                        $select.trigger('change');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar competencias:', error);
                    }
                });
            }
        }

        // Cargar solo los resultados asignados al instructor
        function cargarResultadosAsignadosModal(resultados) {
            const $resultadosSelect = $('#modal-resultados-select');
            $resultadosSelect.empty();
            $resultadosSelect.prop('disabled', false);
            
            if (resultados && resultados.length > 0) {
                resultados.forEach(function(resultado) {
                    const duracion = resultado.duracion || 0;
                    const duracionText = duracion > 0 ? ` (${duracion}h)` : '';
                    $resultadosSelect.append(`<option value="${resultado.id}" data-duracion="${duracion}">${resultado.codigo} - ${resultado.nombre}${duracionText}</option>`);
                });
                
                // Preseleccionar todos los resultados asignados
                const idsArray = resultados.map(r => r.id.toString());
                $resultadosSelect.val(idsArray).trigger('change');
            }
            
            actualizarPanelInfoModal();
        }

        // Evento para cargar resultados cuando se selecciona una competencia
        // Solo se usa cuando NO estamos editando (cuando se asigna un instructor nuevo)
        $(document).on('change', '#modal-competencias-select', function() {
            // Si estamos editando, no cargar resultados desde aquí (ya se cargaron con cargarResultadosAsignadosModal)
            if (instructorFichaIdActual) {
                return;
            }
            
            const competenciaId = $(this).val();
            const $resultadosSelect = $('#modal-resultados-select');
            
            $resultadosSelect.empty().prop('disabled', true);
            
            if (competenciaId) {
                $.ajax({
                    url: `/asignaciones/instructores/competencias/${competenciaId}/resultados`,
                    method: 'GET',
                    data: {
                        ficha_id: fichaId
                    },
                    success: function(response) {
                        $resultadosSelect.empty();
                        $resultadosSelect.prop('disabled', false);
                        
                        if (response.data && response.data.length > 0) {
                            response.data.forEach(function(resultado) {
                                const duracion = resultado.duracion || 0;
                                const duracionText = duracion > 0 ? ` (${duracion}h)` : '';
                                $resultadosSelect.append(`<option value="${resultado.id}" data-duracion="${duracion}">${resultado.codigo} - ${resultado.nombre}${duracionText}</option>`);
                            });
                        }
                        
                        $resultadosSelect.trigger('change');
                        actualizarPanelInfoModal();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar resultados:', error);
                        $resultadosSelect.prop('disabled', false);
                    }
                });
            } else {
                $resultadosSelect.prop('disabled', false);
                actualizarPanelInfoModal();
            }
        });

        // Evento para actualizar panel cuando cambian los resultados
        $(document).on('change', '#modal-resultados-select', function() {
            actualizarPanelInfoModal();
        });

        // Función para actualizar panel de información unificado
        function actualizarPanelInfoModal() {
            // Verificar que el modal esté visible
            if (!$('#modalEditarInstructor').is(':visible')) {
                return;
            }

            const panel = $('#info-content-modal');
            const panelContainer = $('#info-panel-modal');
            
            if (!panel.length || !panelContainer.length) {
                return;
            }
            
            const fechaInicio = $('#modal-fecha-inicio').val();
            const fechaFin = $('#modal-fecha-fin').val();
            const resultadosSeleccionados = $('#modal-resultados-select').val() || [];
            const diasCheckboxes = $('.dia-checkbox-modal:checked');
            
            let html = '';
            let tieneInfo = false;

            // 1. Información de resultados de aprendizaje seleccionados
            if (resultadosSeleccionados.length > 0) {
                tieneInfo = true;
                let totalHorasResultados = 0;
                let detallesResultados = [];

                $('#modal-resultados-select option:selected').each(function() {
                    const duracion = parseFloat($(this).data('duracion')) || 0;
                    totalHorasResultados += duracion;
                    const texto = $(this).text().split(' (')[0];
                    detallesResultados.push({
                        nombre: texto,
                        horas: duracion
                    });
                });

                html += `
                    <div class="mb-3">
                        <h6 class="text-primary">
                            <i class="fas fa-graduation-cap mr-2"></i>Resultados de Aprendizaje Seleccionados
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Resultado</th>
                                        <th class="text-center">Horas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${detallesResultados.map(r => `
                                        <tr>
                                            <td>${r.nombre}</td>
                                            <td class="text-center"><strong>${r.horas}h</strong></td>
                                        </tr>
                                    `).join('')}
                                    <tr class="table-info">
                                        <td><strong>TOTAL</strong></td>
                                        <td class="text-center"><strong>${totalHorasResultados.toFixed(2)}h</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mb-0 mt-2">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Total de horas requeridas:</strong> ${totalHorasResultados.toFixed(2)} horas
                        </div>
                    </div>
                `;
            }

            // 2. Información de horas programadas
            if (fechaInicio && fechaFin && diasCheckboxes.length > 0) {
                tieneInfo = true;
                const horasPorDia = calcularHorasPorDiaModal(diasCheckboxes);
                const horasTrabajadas = calcularHorasTrabajadasModal(fechaInicio, fechaFin, diasCheckboxes);
                
                // Validar que los valores sean números válidos
                const horasPorDiaValido = isNaN(horasPorDia) || horasPorDia <= 0 ? 0 : horasPorDia;
                const horasTrabajadasValido = isNaN(horasTrabajadas) || horasTrabajadas <= 0 ? 0 : horasTrabajadas;
                
                html += `
                    <div class="mb-3">
                        <h6 class="text-success">
                            <i class="fas fa-calendar-check mr-2"></i>Horas Programadas
                        </h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Fecha Inicio:</strong> ${formatearFecha(fechaInicio)}</p>
                                        <p class="mb-1"><strong>Fecha Fin:</strong> ${formatearFecha(fechaFin)}</p>
                                        <p class="mb-0"><strong>Días seleccionados:</strong> ${diasCheckboxes.length}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Horas por día:</strong> ${horasPorDiaValido.toFixed(2)}h</p>
                                        <p class="mb-0"><strong>Total de horas programadas:</strong> <span class="badge badge-success">${horasTrabajadasValido.toFixed(2)}h</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // 3. Comparación y consejos
                if (resultadosSeleccionados.length > 0) {
                    let totalHorasResultados = 0;
                    $('#modal-resultados-select option:selected').each(function() {
                        const duracion = parseFloat($(this).data('duracion')) || 0;
                        totalHorasResultados += duracion;
                    });

                    // Validar que horasTrabajadas sea un número válido
                    const horasTrabajadasValido = isNaN(horasTrabajadas) ? 0 : horasTrabajadas;
                    const diferencia = Math.abs(horasTrabajadasValido - totalHorasResultados);
                    const porcentajeDiferencia = totalHorasResultados > 0 
                        ? (diferencia / totalHorasResultados) * 100 
                        : 0;

                    let alertClass = 'alert-success';
                    let icon = 'fa-check-circle';
                    let mensaje = '';
                    let consejos = [];

                    if (porcentajeDiferencia <= 5) {
                        mensaje = 'Las horas programadas coinciden perfectamente con las horas requeridas.';
                    } else if (porcentajeDiferencia <= 10) {
                        alertClass = 'alert-warning';
                        icon = 'fa-exclamation-triangle';
                        mensaje = `Las horas programadas tienen una diferencia del ${porcentajeDiferencia.toFixed(1)}% con las horas requeridas.`;
                        consejos.push('Ajuste ligeramente las fechas o días de formación para una mejor coincidencia.');
                    } else {
                        alertClass = 'alert-danger';
                        icon = 'fa-times-circle';
                        mensaje = `Las horas programadas tienen una diferencia significativa (${porcentajeDiferencia.toFixed(1)}%) con las horas requeridas.`;
                        
                        if (horasTrabajadasValido < totalHorasResultados) {
                            const horasFaltantes = totalHorasResultados - horasTrabajadasValido;
                            consejos.push(`Faltan ${horasFaltantes.toFixed(2)} horas. Considere:`);
                            consejos.push('- Extender el rango de fechas');
                            consejos.push('- Agregar más días de formación');
                            consejos.push('- Aumentar las horas diarias si es posible');
                        } else {
                            const horasExcedentes = horasTrabajadasValido - totalHorasResultados;
                            consejos.push(`Hay ${horasExcedentes.toFixed(2)} horas excedentes. Considere:`);
                            consejos.push('- Reducir el rango de fechas');
                            consejos.push('- Reducir días de formación');
                            consejos.push('- Seleccionar más resultados de aprendizaje');
                        }
                    }

                    html += `
                        <div class="mb-3">
                            <h6 class="text-warning">
                                <i class="fas fa-balance-scale mr-2"></i>Comparación y Recomendaciones
                            </h6>
                            <div class="alert ${alertClass}">
                                <h6><i class="fas ${icon} mr-2"></i>${mensaje}</h6>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Horas requeridas:</strong> ${totalHorasResultados.toFixed(2)}h</p>
                                        <p class="mb-0"><strong>Horas programadas:</strong> ${horasTrabajadasValido.toFixed(2)}h</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Diferencia:</strong> ${diferencia.toFixed(2)}h</p>
                                        <p class="mb-0"><strong>Porcentaje:</strong> ${porcentajeDiferencia.toFixed(1)}%</p>
                                    </div>
                                </div>
                                ${consejos.length > 0 ? `
                                    <hr>
                                    <p class="mb-1"><strong>Consejos:</strong></p>
                                    <ul class="mb-0">
                                        ${consejos.map(c => `<li>${c}</li>`).join('')}
                                    </ul>
                                ` : ''}
                            </div>
                        </div>
                    `;
                }
            }

            // Si no hay información, mostrar mensaje
            if (!tieneInfo) {
                html = `
                    <div class="text-muted text-center">
                        <i class="fas fa-arrow-up mr-2"></i>
                        Complete los campos arriba para ver información y consejos
                    </div>
                `;
            }

            panel.html(html);
            panelContainer.show();
        }

        // Función para calcular horas trabajadas en el modal
        function calcularHorasTrabajadasModal(fechaInicio, fechaFin, diasCheckboxes) {
            try {
                if (!fechaInicio || !fechaFin || !diasCheckboxes || diasCheckboxes.length === 0) {
                    return 0;
                }

                const fechaInicioObj = new Date(fechaInicio + 'T00:00:00');
                const fechaFinObj = new Date(fechaFin + 'T00:00:00');
                
                if (isNaN(fechaInicioObj.getTime()) || isNaN(fechaFinObj.getTime())) {
                    console.error('Fechas inválidas:', fechaInicio, fechaFin);
                    return 0;
                }
                
                if (fechaInicioObj > fechaFinObj) {
                    return 0;
                }

                const mapeoDias = {
                    12: 1, 13: 2, 14: 3, 15: 4, 16: 5, 17: 6, 18: 0
                };

                // Convertir jQuery object a array si es necesario
                const checkboxesArray = diasCheckboxes instanceof jQuery 
                    ? diasCheckboxes.toArray() 
                    : Array.isArray(diasCheckboxes) 
                        ? diasCheckboxes 
                        : Array.from(diasCheckboxes);
                
                const diasSeleccionados = checkboxesArray.map(cb => {
                    const value = cb instanceof jQuery ? cb.val() : cb.value;
                    return parseInt(value);
                }).filter(id => !isNaN(id));
                
                if (diasSeleccionados.length === 0) {
                    return 0;
                }

                const horasPorDia = calcularHorasPorDiaModal(diasCheckboxes);
                
                if (isNaN(horasPorDia) || horasPorDia <= 0) {
                    console.error('Horas por día inválidas:', horasPorDia);
                    return 0;
                }
                
                let diasEfectivos = 0;
                const fechaActual = new Date(fechaInicioObj);
                
                while (fechaActual <= fechaFinObj) {
                    const diaSemana = fechaActual.getDay();
                    const diaSeleccionado = diasSeleccionados.find(diaId => mapeoDias[diaId] === diaSemana);
                    if (diaSeleccionado) {
                        diasEfectivos++;
                    }
                    fechaActual.setDate(fechaActual.getDate() + 1);
                }

                const total = diasEfectivos * horasPorDia;
                return isNaN(total) ? 0 : total;
            } catch (e) {
                console.error('Error calculando horas trabajadas:', e);
                return 0;
            }
        }

        // Función para calcular horas por día en el modal
        function calcularHorasPorDiaModal(diasCheckboxes) {
            try {
                if (!diasCheckboxes || diasCheckboxes.length === 0) {
                    return 0;
                }

                const diasData = window.diasSemanaData || {};
                let totalHoras = 0;
                let diasConHorario = 0;

                // Convertir jQuery object a array si es necesario
                const checkboxesArray = diasCheckboxes instanceof jQuery 
                    ? diasCheckboxes.toArray() 
                    : Array.isArray(diasCheckboxes) 
                        ? diasCheckboxes 
                        : Array.from(diasCheckboxes);

                checkboxesArray.forEach(function(checkbox) {
                    const diaId = checkbox instanceof jQuery 
                        ? parseInt(checkbox.val()) 
                        : parseInt(checkbox.value || $(checkbox).val());
                    
                    if (isNaN(diaId)) {
                        return;
                    }

                    const horaInicioInput = $(`input[name="hora_inicio_${diaId}"]`);
                    const horaFinInput = $(`input[name="hora_fin_${diaId}"]`);
                    
                    let horaInicio, horaFin;
                    
                    if (horaInicioInput.length && horaInicioInput.val()) {
                        horaInicio = horaInicioInput.val();
                    } else {
                        const diaData = diasData[diaId];
                        horaInicio = diaData ? diaData.hora_inicio : null;
                    }
                    
                    if (horaFinInput.length && horaFinInput.val()) {
                        horaFin = horaFinInput.val();
                    } else {
                        const diaData = diasData[diaId];
                        horaFin = diaData ? diaData.hora_fin : null;
                    }
                    
                    if (horaInicio && horaFin) {
                        const horas = convertirTiempoAHorasModal(horaInicio, horaFin);
                        if (!isNaN(horas) && horas > 0) {
                            totalHoras += horas;
                            diasConHorario++;
                        }
                    }
                });

                if (diasConHorario === 0) {
                    return 0;
                }

                const promedio = totalHoras / diasConHorario;
                return isNaN(promedio) ? 0 : promedio;
            } catch (e) {
                console.error('Error calculando horas por día:', e);
                return 0;
            }
        }

        // Función para convertir tiempo a horas en el modal
        function convertirTiempoAHorasModal(horaInicio, horaFin) {
            try {
                if (!horaInicio || !horaFin) {
                    return 0;
                }

                // Asegurar formato HH:MM
                const horaInicioStr = horaInicio.length === 5 ? horaInicio : horaInicio + ':00';
                const horaFinStr = horaFin.length === 5 ? horaFin : horaFin + ':00';

                const inicio = new Date(`2000-01-01T${horaInicioStr}`);
                let fin = new Date(`2000-01-01T${horaFinStr}`);
                
                if (isNaN(inicio.getTime()) || isNaN(fin.getTime())) {
                    console.error('Horas inválidas:', horaInicio, horaFin);
                    return 0;
                }
                
                if (fin < inicio) {
                    fin = new Date(fin.getTime() + 24 * 60 * 60 * 1000);
                }
                
                const diferencia = fin - inicio;
                const horas = diferencia / (1000 * 60 * 60);
                
                return isNaN(horas) ? 0 : Math.max(0, horas);
            } catch (e) {
                console.error('Error convirtiendo tiempo a horas:', e, horaInicio, horaFin);
                return 0;
            }
        }

        // Función para formatear fecha
        function formatearFecha(fechaStr) {
            try {
                const fecha = new Date(fechaStr + 'T00:00:00');
                const opciones = { year: 'numeric', month: '2-digit', day: '2-digit' };
                return fecha.toLocaleDateString('es-ES', opciones);
            } catch (e) {
                return fechaStr;
            }
        }

        // Guardar cambios del instructor (unificado)
        $('#btn-guardar-instructor-modal').click(function() {
            const fechaInicio = $('#modal-fecha-inicio').val();
            const fechaFin = $('#modal-fecha-fin').val();
            const competenciaId = $('#modal-competencias-select').val();
            const resultadosIds = $('#modal-resultados-select').val() || [];
            const diasSeleccionados = obtenerDiasSeleccionadosModal();
            
            // Validaciones básicas
            if (!fechaInicio || !fechaFin) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Fechas requeridas',
                    text: 'Debe seleccionar fecha de inicio y fin'
                });
                return;
            }
            
            if (diasSeleccionados.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Días requeridos',
                    text: 'Debe seleccionar al menos un día de formación'
                });
                return;
            }

            Swal.fire({
                title: 'Guardando...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Preparar datos para enviar - convertir días al formato esperado
            const diasFormato = {};
            diasSeleccionados.forEach(dia => {
                diasFormato[dia.dia_id] = {
                    hora_inicio: dia.hora_inicio || null,
                    hora_fin: dia.hora_fin || null
                };
            });

            const datos = {
                _token: '{{ csrf_token() }}',
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                competencia_id: competenciaId || null,
                resultados_aprendizaje: resultadosIds,
                dias: diasFormato
            };

            $.ajax({
                url: `/fichaCaracterizacion/${fichaId}/instructor/${instructorFichaIdActual}/actualizar-asignacion`,
                method: 'POST',
                data: datos,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message || 'Asignación actualizada correctamente',
                            confirmButtonText: 'Entendido'
                        }).then(() => {
                            $('#modalEditarInstructor').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: response.message || 'Error al actualizar la asignación'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Error al guardar los cambios';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMsg = '<ul class="text-left">';
                        Object.keys(errors).forEach(key => {
                            errors[key].forEach(msg => {
                                errorMsg += `<li>${msg}</li>`;
                            });
                        });
                        errorMsg += '</ul>';
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        html: errorMsg
                    });
                }
            });
        });

        // Evento para abrir modal unificado
        $(document).on('click', '.btn-editar-instructor', function() {
            const instructorFichaId = $(this).data('instructor-ficha-id');
            const instructorNombre = $(this).data('instructor-nombre');
            const fechaInicio = $(this).data('fecha-inicio');
            const fechaFin = $(this).data('fecha-fin');
            const competenciaId = $(this).data('competencia-id');
            const resultadosIds = $(this).data('resultados-ids');
            
            abrirModalEditarInstructor(instructorFichaId, instructorNombre, fechaInicio, fechaFin, competenciaId, resultadosIds);
        });

        // Limpiar formulario del modal
        function limpiarFormularioModal() {
            $('.dia-checkbox-modal').prop('checked', false);
            $('.hora-inicio-modal, .hora-fin-modal').val('').prop('disabled', true);
            $('.dia-row-modal').removeClass('table-active');
            $('.dia-status-modal').hide();
            $('#preview-fechas-modal').hide();
            $('#select-all-modal').prop('checked', false);
        }

        // Cargar días ya asignados
        function cargarDiasAsignados(instructorFichaId) {
            $.ajax({
                url: `/fichaCaracterizacion/${fichaId}/instructor/${instructorFichaId}/obtener-dias`,
                method: 'GET',
                success: function(response) {
                    if (response.success && response.dias.length > 0) {
                        response.dias.forEach(function(dia) {
                            const $checkbox = $(`.dia-checkbox-modal[value="${dia.dia_id}"]`);
                            $checkbox.prop('checked', true).trigger('change');
                            
                            if (dia.hora_inicio) {
                                $(`input[name="hora_inicio_${dia.dia_id}"]`).val(dia.hora_inicio);
                            }
                            if (dia.hora_fin) {
                                $(`input[name="hora_fin_${dia.dia_id}"]`).val(dia.hora_fin);
                            }
                        });
                    }
                },
                error: function() {
                    console.log('No se pudieron cargar los días asignados');
                }
            });
        }

        // Obtener días seleccionados del modal
        function obtenerDiasSeleccionadosModal() {
            const dias = [];
            $('.dia-checkbox-modal:checked').each(function() {
                const diaId = $(this).val();
                const horaInicio = $(`input[name="hora_inicio_${diaId}"]`).val();
                const horaFin = $(`input[name="hora_fin_${diaId}"]`).val();
                
                dias.push({
                    dia_id: parseInt(diaId),
                    hora_inicio: horaInicio || null,
                    hora_fin: horaFin || null
                });
            });
            return dias;
        }

        // Mostrar preview de fechas en modal
        function mostrarPreviewFechasModal(fechas, total) {
            let html = `<div class="alert alert-info"><strong><i class="fas fa-calendar-check"></i> Se generarán ${total} sesiones de formación</strong></div>`;
            html += '<div class="table-responsive"><table class="table table-sm table-striped table-bordered">';
            html += '<thead class="thead-light"><tr><th width="5%">#</th><th width="20%">Fecha</th><th width="25%">Día</th><th width="25%">Horario</th></tr></thead><tbody>';
            
            fechas.forEach((fecha, index) => {
                const horario = fecha.hora_inicio && fecha.hora_fin 
                    ? `${fecha.hora_inicio} - ${fecha.hora_fin}` 
                    : '<span class="text-muted">Sin horario</span>';
                
                html += `<tr>
                    <td class="text-center">${index + 1}</td>
                    <td><strong>${fecha.fecha}</strong></td>
                    <td>${fecha.dia_semana}</td>
                    <td>${horario}</td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            
            $('#fechas-container-modal').html(html);
            $('#preview-fechas-modal').slideDown();
        }

        // Guardar asignación desde el modal
        function guardarAsignacionModal(diasSeleccionados) {
            Swal.fire({
                title: 'Guardando...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/fichaCaracterizacion/${fichaId}/instructor/${instructorFichaIdActual}/asignar-dias`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    dias: diasSeleccionados
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            html: `
                                <p>${response.message}</p>
                                <p class="mb-0"><strong>Total de sesiones programadas:</strong> ${response.total_sesiones || 0}</p>
                            `,
                            confirmButtonText: 'Entendido'
                        }).then(() => {
                            $('#modalDiasFormacion').modal('hide');
                            location.reload(); // Recargar para mostrar los días actualizados
                        });
                    } else {
                        mostrarErrorConflictosModal(response);
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Error al guardar los días de formación';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: errorMsg
                    });
                }
            });
        }

        // Mostrar errores de conflictos
        function mostrarErrorConflictosModal(response) {
            let html = `<p>${response.message}</p>`;
            
            if (response.conflictos && response.conflictos.length > 0) {
                html += '<hr><p><strong>Conflictos detectados:</strong></p><ul class="text-left">';
                response.conflictos.forEach(conflicto => {
                    html += `<li>${conflicto.dia_nombre}: Ficha ${conflicto.ficha_conflicto} (${conflicto.horario_conflicto})</li>`;
                });
                html += '</ul>';
            }
            
            Swal.fire({
                icon: 'error',
                title: 'No se pudo asignar',
                html: html
            });
        }
    </script>
@endsection
