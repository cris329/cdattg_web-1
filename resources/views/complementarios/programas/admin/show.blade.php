@extends('adminlte::page')

@section('title', 'Detalles del Programa Complementario')

@section('css')
    @vite(['resources/css/parametros.css'])
    <style>
        .program-header {
            background: linear-gradient(135deg, #00794d 0%, #005235 100%);
            color: white;
            padding: 2rem;
            border-radius: 0.5rem 0.5rem 0 0;
            border-bottom: 2px solid #005235;
        }

        .info-box-modern {
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid #00794d;
            height: 100%;
        }

        .info-box-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,121,77,0.15);
        }

        .info-box-modern .icon {
            font-size: 2.5rem;
            opacity: 0.8;
            margin-bottom: 0.5rem;
            color: #00794d;
        }

        .info-box-modern .label {
            font-size: 0.875rem;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .info-box-modern .value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #00794d;
        }

        .section-card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .section-card .card-header {
            background: linear-gradient(135deg, #f4f9f7 0%, #e1efda 100%);
            border-bottom: 2px solid #00794d;
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: #005235;
        }

        .competencia-item, .rap-item {
            background: white;
            border: 1px solid #e1efda;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
            transition: all 0.2s;
            border-left: 4px solid;
        }

        .competencia-item {
            border-left-color: #00794d;
        }

        .competencia-item:hover {
            background: #f4f9f7;
            border-left-color: #005235;
            transform: translateX(5px);
        }

        .rap-item {
            border-left-color: #00a859;
        }

        .rap-item:hover {
            background: #f4f9f7;
            border-left-color: #00794d;
            transform: translateX(5px);
        }

        .badge-modern {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .description-box {
            background: #f4f9f7;
            border-left: 4px solid #00794d;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin: 1rem 0;
        }

        .action-buttons {
            position: sticky;
            top: 1rem;
            z-index: 10;
        }
    </style>
@endsection

@section('content_header')
    <x-page-header icon="fa-graduation-cap" title="Detalles del Programa"
        subtitle="Información completa del programa complementario" :breadcrumb="[
            ['label' => 'Inicio', 'url' => route('verificarLogin'), 'icon' => 'fa-home'],
            [
                'label' => 'Programas Complementarios',
                'url' => route('complementarios-ofertados.index'),
                'icon' => 'fa-graduation-cap',
            ],
            ['label' => 'Detalles', 'icon' => 'fa-eye', 'active' => true],
        ]" />
@endsection

@section('content')
    <section class="content mt-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!-- Header del Programa -->
                    <div class="card shadow-lg mb-4" style="border: none; overflow: hidden;">
                        <div class="program-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h2 class="mb-2 font-weight-bold">
                                        <i class="fas fa-graduation-cap mr-2"></i>{{ $programa->nombre }}
                                    </h2>
                                    <div class="d-flex flex-wrap align-items-center gap-3 mt-3">
                                        <span class="badge badge-light badge-modern" style="background-color: rgba(255,255,255,0.2); color: white;">
                                            <i class="fas fa-hashtag mr-1"></i>{{ $programa->codigo }}
                                        </span>
                                        <span class="badge badge-pill badge-modern" style="background-color: rgba(255,255,255,0.2); color: white;">
                                            <i class="fas fa-info-circle mr-1"></i>{{ $programa->estado_label }}
                                        </span>
                                    </div>
                                </div>
                                <div class="action-buttons">
                                    <a href="{{ route('complementarios-ofertados.edit', $programa->id) }}"
                                        class="btn btn-warning btn-lg shadow-sm">
                                        <i class="fas fa-edit mr-2"></i> Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Columna Principal -->
                        <div class="col-lg-8">
                            <!-- Información General -->
                            <div class="card section-card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-info-circle mr-2"></i>Información General
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($programa->descripcion)
                                    <div class="description-box">
                                        <h6 class="font-weight-bold mb-2">
                                            <i class="fas fa-align-left mr-2"></i>Descripción
                                        </h6>
                                        <p class="mb-0 text-muted">{{ $programa->descripcion }}</p>
                                    </div>
                                    @endif

                                    @if($programa->justificacion)
                                    <div class="description-box">
                                        <h6 class="font-weight-bold mb-2">
                                            <i class="fas fa-lightbulb mr-2"></i>Justificación
                                        </h6>
                                        <p class="mb-0 text-muted">{{ $programa->justificacion }}</p>
                                    </div>
                                    @endif

                                    @if($programa->requisitos_ingreso)
                                    <div class="description-box">
                                        <h6 class="font-weight-bold mb-2">
                                            <i class="fas fa-clipboard-check mr-2"></i>Requisitos de Ingreso
                                        </h6>
                                        <p class="mb-0 text-muted">{{ $programa->requisitos_ingreso }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Competencias -->
                            @if($programa->competencias && $programa->competencias->count() > 0)
                            <div class="card section-card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-tasks mr-2"></i>Competencias
                                        <span class="badge ml-2" style="background-color: #00794d; color: white;">{{ $programa->competencias->count() }}</span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @foreach($programa->competencias as $competencia)
                                    <div class="competencia-item">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <span class="badge mr-2" style="background-color: #00794d; color: white;">{{ $competencia->codigo }}</span>
                                                    <strong>{{ $competencia->nombre }}</strong>
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Resultados de Aprendizaje -->
                            @if($programa->raps && $programa->raps->count() > 0)
                            <div class="card section-card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-check-circle mr-2"></i>Resultados de Aprendizaje (RAPs)
                                        <span class="badge ml-2" style="background-color: #00a859; color: white;">{{ $programa->raps->count() }}</span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @foreach($programa->raps as $rap)
                                    <div class="rap-item">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <span class="badge mr-2" style="background-color: #00a859; color: white;">{{ $rap->codigo }}</span>
                                                    <strong>{{ $rap->nombre }}</strong>
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Configuración Académica -->
                            <div class="card section-card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-cog mr-2"></i>Configuración
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="info-box-modern">
                                                <div class="icon">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                                <div class="label">Duración</div>
                                                <div class="value">{{ $programa->duracion }} horas</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="info-box-modern">
                                                <div class="icon">
                                                    <i class="fas fa-users"></i>
                                                </div>
                                                <div class="label">Cupos Disponibles</div>
                                                <div class="value">{{ $programa->cupos }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-chalkboard-teacher mr-2"></i>
                                            <strong class="text-muted small">Modalidad</strong>
                                        </div>
                                        <p class="mb-0 font-weight-bold">
                                            {{ $programa->modalidad->parametro->name ?? 'N/A' }}
                                        </p>
                                    </div>

                                    <hr>

                                    <div class="mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-calendar-alt mr-2"></i>
                                            <strong class="text-muted small">Jornada</strong>
                                        </div>
                                        <p class="mb-0 font-weight-bold">
                                            {{ $programa->jornada->jornada ?? 'N/A' }}
                                        </p>
                                    </div>

                                    <hr>

                                    <div class="mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-building mr-2"></i>
                                            <strong class="text-muted small">Ambiente</strong>
                                        </div>
                                        <p class="mb-0 font-weight-bold">
                                            @if($programa->ambiente)
                                                {{ $programa->ambiente->title }}
                                                @if($programa->ambiente->piso)
                                                    <br><small class="text-muted">{{ $programa->ambiente->piso->piso }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </p>
                                    </div>

                                    @if($programa->diasFormacion && $programa->diasFormacion->count() > 0)
                                    <hr>
                                    <div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-calendar-week mr-2"></i>
                                            <strong class="text-muted small">Días de Formación</strong>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($programa->diasFormacion as $dia)
                                            <span class="badge badge-modern" style="background-color: #00794d; color: white;">
                                                <i class="fas fa-calendar-day mr-1"></i>
                                                {{ $dia->name ?? 'Día' }}
                                                <small class="ml-1">({{ $dia->pivot->hora_inicio }} - {{ $dia->pivot->hora_fin }})</small>
                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Columna Lateral -->
                        <div class="col-lg-4">
                            <!-- Acciones -->
                            <div class="card section-card">
                                <div class="card-body">
                                    <a href="{{ route('complementarios-ofertados.index') }}"
                                        class="btn btn-secondary btn-block mb-2">
                                        <i class="fas fa-arrow-left mr-2"></i> Volver al Listado
                                    </a>
                                    <a href="{{ route('complementarios-ofertados.edit', $programa->id) }}"
                                        class="btn btn-warning btn-block">
                                        <i class="fas fa-edit mr-2"></i> Editar Programa
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

