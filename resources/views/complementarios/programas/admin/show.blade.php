@extends('adminlte::page')

@section('title', 'Detalles del Programa Complementario')

@section('css')
    {{-- Nota: esta vista busca conservar el look & feel nativo de AdminLTE --}}
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
                    {{-- Encabezado (AdminLTE) --}}
                    <div class="card card-outline card-success shadow-sm mb-3">
                        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-md-between">
                            <div class="mb-2 mb-md-0">
                                <h3 class="card-title mb-1">
                                    <i class="fas fa-graduation-cap mr-2"></i>{{ $programa->nombre }}
                                </h3>
                                <div class="d-flex flex-wrap align-items-center">
                                    <span class="badge badge-secondary mr-2 mb-1">
                                        <i class="fas fa-hashtag mr-1"></i>{{ $programa->codigo }}
                                    </span>
                                    <span class="badge badge-pill {{ $programa->badge_class ?? 'badge-info' }} mb-1">
                                        <i class="fas fa-info-circle mr-1"></i>{{ $programa->estado_label }}
                                    </span>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap">
                                <a href="{{ route('complementarios-ofertados.edit', $programa->id) }}"
                                    class="btn btn-warning text-white mr-2 mb-2 mb-md-0">
                                    <i class="fas fa-edit mr-1"></i> Editar
                                </a>
                                <a href="{{ route('complementarios-ofertados.index') }}" class="btn btn-secondary mb-2 mb-md-0">
                                    <i class="fas fa-arrow-left mr-1"></i> Volver
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Columna Principal -->
                        <div class="col-lg-8">
                            <!-- Información General -->
                            <div class="card card-outline card-info shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title mb-0">
                                        <i class="fas fa-info-circle mr-2"></i>Información general
                                    </h3>
                                </div>
                                <div class="card-body">
                                    @if($programa->justificacion)
                                        <div class="callout callout-info mb-3">
                                            <h6 class="mb-2 font-weight-bold">
                                                <i class="fas fa-align-left mr-2"></i>Descripción
                                            </h6>
                                            <p class="mb-0 text-muted">{{ $programa->justificacion }}</p>
                                        </div>
                                    @endif

                                    @if($programa->requisitos_ingreso)
                                        <div class="callout callout-info mb-0">
                                            <h6 class="mb-2 font-weight-bold">
                                                <i class="fas fa-list-check mr-2"></i>Requisitos de ingreso
                                            </h6>
                                            <p class="mb-0 text-muted">{{ $programa->requisitos_ingreso }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Competencias -->
                            @if($programa->competencias && $programa->competencias->count() > 0)
                                <div class="card card-outline card-success shadow-sm">
                                    <div class="card-header">
                                        <h3 class="card-title mb-0">
                                            <i class="fas fa-tasks mr-2"></i>Competencias
                                            <span class="badge badge-success ml-2">{{ $programa->competencias->count() }}</span>
                                        </h3>
                                    </div>
                                    <div class="card-body p-0">
                                        <ul class="list-group list-group-flush">
                                            @foreach($programa->competencias as $competencia)
                                                <li class="list-group-item">
                                                    <span class="badge badge-success mr-2">{{ $competencia->codigo }}</span>
                                                    <strong>{{ $competencia->nombre }}</strong>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif

                            <!-- Resultados de Aprendizaje -->
                            @if($programa->raps && $programa->raps->count() > 0)
                                <div class="card card-outline card-primary shadow-sm">
                                    <div class="card-header">
                                        <h3 class="card-title mb-0">
                                            <i class="fas fa-check-circle mr-2"></i>Resultados de aprendizaje (RAPs)
                                            <span class="badge badge-primary ml-2">{{ $programa->raps->count() }}</span>
                                        </h3>
                                    </div>
                                    <div class="card-body p-0">
                                        <ul class="list-group list-group-flush">
                                            @foreach($programa->raps as $rap)
                                                <li class="list-group-item">
                                                    <span class="badge badge-primary mr-2">{{ $rap->codigo }}</span>
                                                    <strong>{{ $rap->nombre }}</strong>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif

                            <!-- Configuración Académica -->
                            <div class="card card-outline card-secondary shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title mb-0">
                                        <i class="fas fa-cog mr-2"></i>Configuración
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="info-box bg-light shadow-none">
                                                <span class="info-box-icon bg-info"><i class="fas fa-clock"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted text-uppercase small">Duración</span>
                                                    <span class="info-box-number">{{ $programa->duracion }} horas</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="info-box bg-light shadow-none">
                                                <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted text-uppercase small">Cupos</span>
                                                    <span class="info-box-number">{{ $programa->cupos }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <dl class="row mb-0">
                                        <dt class="col-sm-4 text-muted">Modalidad</dt>
                                        <dd class="col-sm-8 mb-2">{{ $programa->modalidad->parametro->name ?? 'N/A' }}</dd>

                                        <dt class="col-sm-4 text-muted">Jornada</dt>
                                        <dd class="col-sm-8 mb-2">{{ $programa->jornada->jornada ?? 'N/A' }}</dd>

                                        <dt class="col-sm-4 text-muted">Ambiente</dt>
                                        <dd class="col-sm-8 mb-2">
                                            @if($programa->ambiente)
                                                {{ $programa->ambiente->title }}
                                                @if($programa->ambiente->piso)
                                                    <br><small class="text-muted">{{ $programa->ambiente->piso->piso }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif

                                            @if($programa->ambiente_comentario)
                                                <div class="mt-2">
                                                    <small class="text-muted d-block mb-1">
                                                        <i class="fas fa-comment-alt mr-1"></i>Comentario:
                                                    </small>
                                                    <span class="text-muted">{{ $programa->ambiente_comentario }}</span>
                                                </div>
                                            @endif
                                        </dd>
                                    </dl>

                                    @if($programa->diasFormacion && $programa->diasFormacion->count() > 0)
                                        <hr>
                                        <h6 class="text-muted text-uppercase small mb-2">
                                            <i class="fas fa-calendar-week mr-1"></i>Días de formación
                                        </h6>
                                        <div class="d-flex flex-wrap">
                                            @foreach($programa->diasFormacion as $dia)
                                                <span class="badge badge-success mr-2 mb-2">
                                                    <i class="fas fa-calendar-day mr-1"></i>
                                                    {{ $dia->parametro->name ?? 'Día' }}
                                                    <small class="ml-1">({{ $dia->pivot->hora_inicio }} - {{ $dia->pivot->hora_fin }})</small>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Columna Lateral -->
                        <div class="col-lg-4">
                            <!-- Acciones -->
                            <div class="card card-outline card-warning shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title mb-0">
                                        <i class="fas fa-bolt mr-2"></i>Acciones
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <a href="{{ route('complementarios-ofertados.index') }}"
                                        class="btn btn-secondary btn-block mb-2">
                                        <i class="fas fa-arrow-left mr-2"></i> Volver al listado
                                    </a>
                                    <a href="{{ route('complementarios-ofertados.edit', $programa->id) }}"
                                        class="btn btn-warning text-white btn-block">
                                        <i class="fas fa-edit mr-2"></i> Editar programa
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
